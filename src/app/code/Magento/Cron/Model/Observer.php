<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Handling cron jobs
 */
namespace Magento\Cron\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

class Observer
{
    /**#@+
     * Cache key values
     */
    const CACHE_KEY_LAST_SCHEDULE_GENERATE_AT = 'cron_last_schedule_generate_at';

    const CACHE_KEY_LAST_HISTORY_CLEANUP_AT = 'cron_last_history_cleanup_at';

    /**#@-*/

    /**#@+
     * List of configurable constants used to calculate and validate during handling cron jobs
     */
    const XML_PATH_SCHEDULE_GENERATE_EVERY = 'schedule_generate_every';

    const XML_PATH_SCHEDULE_AHEAD_FOR = 'schedule_ahead_for';

    const XML_PATH_SCHEDULE_LIFETIME = 'schedule_lifetime';

    const XML_PATH_HISTORY_CLEANUP_EVERY = 'history_cleanup_every';

    const XML_PATH_HISTORY_SUCCESS = 'history_success_lifetime';

    const XML_PATH_HISTORY_FAILURE = 'history_failure_lifetime';

    /**#@-*/

    /**
     * Value of seconds in one minute
     */
    const SECONDS_IN_MINUTE = 60;

    /**
     * @var \Magento\Cron\Model\Resource\Schedule\Collection
     */
    protected $_pendingSchedules;

    /**
     * @var \Magento\Cron\Model\ConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cache;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var ScheduleFactory
     */
    protected $_scheduleFactory;

    /**
     * @var \Magento\Framework\App\Console\Request
     */
    protected $_request;

    /**
     * @var \Magento\Framework\ShellInterface
     */
    protected $_shell;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param ScheduleFactory $scheduleFactory
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param ConfigInterface $config
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Console\Request $request
     * @param \Magento\Framework\ShellInterface $shell
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Cron\Model\ScheduleFactory $scheduleFactory,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Cron\Model\ConfigInterface $config,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Console\Request $request,
        \Magento\Framework\ShellInterface $shell
    ) {
        $this->_objectManager = $objectManager;
        $this->_scheduleFactory = $scheduleFactory;
        $this->_cache = $cache;
        $this->_config = $config;
        $this->_scopeConfig = $scopeConfig;
        $this->_request = $request;
        $this->_shell = $shell;
    }

    /**
     * Process cron queue
     * Generate tasks schedule
     * Cleanup tasks schedule
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function dispatch($observer)
    {
        $pendingJobs = $this->_getPendingSchedules();
        $currentTime = time();
        $jobGroupsRoot = $this->_config->getJobs();

        foreach ($jobGroupsRoot as $groupId => $jobsRoot) {
            if ($this->_request->getParam(
                'group'
            ) === null && $this->_scopeConfig->getValue(
                'system/cron/' . $groupId . '/use_separate_process',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ) == 1
            ) {
                $this->_shell->execute(
                    '%s -f %s -- --group=%s',
                    array(
                        PHP_BINARY,
                        BP . '/' . DirectoryList::PUB . '/cron.php',
                        $groupId
                    )
                );
                continue;
            }
            if ($this->_request->getParam('group') !== null && $this->_request->getParam('group') != $groupId) {
                continue;
            }

            foreach ($pendingJobs as $schedule) {
                $jobConfig = isset($jobsRoot[$schedule->getJobCode()]) ? $jobsRoot[$schedule->getJobCode()] : null;
                if (!$jobConfig) {
                    continue;
                }

                $scheduledTime = strtotime($schedule->getScheduledAt());
                if ($scheduledTime > $currentTime || !$schedule->tryLockJob()) {
                    continue;
                }

                try {
                    $this->_runJob($scheduledTime, $currentTime, $jobConfig, $schedule, $groupId);
                } catch (\Exception $e) {
                    $schedule->setMessages($e->getMessage());
                }
                $schedule->save();
            }

            $this->_generate($groupId);
            $this->_cleanup($groupId);
        }
    }

    /**
     * Execute job by calling specific class::method
     *
     * @param int $scheduledTime
     * @param int $currentTime
     * @param string[] $jobConfig
     * @param Schedule $schedule
     * @param string $groupId
     * @return void
     * @throws \Exception
     */
    protected function _runJob($scheduledTime, $currentTime, $jobConfig, $schedule, $groupId)
    {
        $scheduleLifetime = (int)$this->_scopeConfig->getValue(
            'system/cron/' . $groupId . '/' . self::XML_PATH_SCHEDULE_LIFETIME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $scheduleLifetime = $scheduleLifetime * self::SECONDS_IN_MINUTE;
        if ($scheduledTime < $currentTime - $scheduleLifetime) {
            $schedule->setStatus(Schedule::STATUS_MISSED);
            throw new \Exception('Too late for the schedule');
        }

        if (!isset($jobConfig['instance'], $jobConfig['method'])) {
            $schedule->setStatus(Schedule::STATUS_ERROR);
            throw new \Exception('No callbacks found');
        }
        $model = $this->_objectManager->create($jobConfig['instance']);
        $callback = array($model, $jobConfig['method']);
        if (!is_callable($callback)) {
            $schedule->setStatus(Schedule::STATUS_ERROR);
            throw new \Exception(
                sprintf('Invalid callback: %s::%s can\'t be called', $jobConfig['instance'], $jobConfig['method'])
            );
        }

        $schedule->setExecutedAt(strftime('%Y-%m-%d %H:%M:%S', time()))->save();

        call_user_func_array($callback, array($schedule));

        $schedule->setStatus(Schedule::STATUS_SUCCESS)->setFinishedAt(strftime('%Y-%m-%d %H:%M:%S', time()));
    }

    /**
     * Return job collection from data base with status 'pending'
     *
     * @return \Magento\Cron\Model\Resource\Schedule\Collection
     */
    protected function _getPendingSchedules()
    {
        if (!$this->_pendingSchedules) {
            $this->_pendingSchedules = $this->_scheduleFactory->create()->getCollection()->addFieldToFilter(
                'status',
                Schedule::STATUS_PENDING
            )->load();
        }
        return $this->_pendingSchedules;
    }

    /**
     * Generate cron schedule
     *
     * @param string $groupId
     * @return $this
     */
    protected function _generate($groupId)
    {
        /**
         * check if schedule generation is needed
         */
        $lastRun = (int)$this->_cache->load(self::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT . $groupId);
        $rawSchedulePeriod = (int)$this->_scopeConfig->getValue(
            'system/cron/' . $groupId . '/' . self::XML_PATH_SCHEDULE_GENERATE_EVERY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $schedulePeriod = $rawSchedulePeriod * self::SECONDS_IN_MINUTE;
        if ($lastRun > time() - $schedulePeriod) {
            return $this;
        }

        $schedules = $this->_getPendingSchedules();
        $exists = array();
        /** @var Schedule $schedule */
        foreach ($schedules as $schedule) {
            $exists[$schedule->getJobCode() . '/' . $schedule->getScheduledAt()] = 1;
        }

        /**
         * generate global crontab jobs
         */
        $jobs = $this->_config->getJobs();
        $this->_generateJobs($jobs[$groupId], $exists, $groupId);

        /**
         * save time schedules generation was ran with no expiration
         */
        $this->_cache->save(time(), self::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT . $groupId, array('crontab'), null);

        return $this;
    }

    /**
     * Generate jobs for config information
     *
     * @param   array $jobs
     * @param   array $exists
     * @param   string $groupId
     * @return  $this
     */
    protected function _generateJobs($jobs, $exists, $groupId)
    {
        foreach ($jobs as $jobCode => $jobConfig) {
            $cronExpression = null;
            if (isset($jobConfig['config_path'])) {
                $cronExpression = $this->getConfigSchedule($jobConfig) ? : null;
            }

            if (!$cronExpression) {
                if (isset($jobConfig['schedule'])) {
                    $cronExpression = $jobConfig['schedule'];
                }
            }

            if (!$cronExpression) {
                continue;
            }

            $timeInterval = $this->getScheduleTimeInterval($groupId);
            $this->saveSchedule($jobCode, $cronExpression, $timeInterval, $exists);
        }
        return $this;
    }

    /**
     * Clean existed jobs
     *
     * @param string $groupId
     * @return $this
     */
    protected function _cleanup($groupId)
    {
        // check if history cleanup is needed
        $lastCleanup = (int)$this->_cache->load(self::CACHE_KEY_LAST_HISTORY_CLEANUP_AT . $groupId);
        $historyCleanUp = (int)$this->_scopeConfig->getValue(
            'system/cron/' . $groupId . '/' . self::XML_PATH_HISTORY_CLEANUP_EVERY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($lastCleanup > time() - $historyCleanUp * self::SECONDS_IN_MINUTE) {
            return $this;
        }

        /**
         * @var \Magento\Cron\Model\Resource\Schedule\Collection $history
         */
        $history = $this->_scheduleFactory->create()->getCollection()->addFieldToFilter(
            'status',
            array('in' => array(Schedule::STATUS_SUCCESS, Schedule::STATUS_MISSED, Schedule::STATUS_ERROR))
        )->load();

        $historySuccess = (int)$this->_scopeConfig->getValue(
            'system/cron/' . $groupId . '/' . self::XML_PATH_HISTORY_SUCCESS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $historyFailure = (int)$this->_scopeConfig->getValue(
            'system/cron/' . $groupId . '/' . self::XML_PATH_HISTORY_FAILURE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $historyLifetimes = array(
            Schedule::STATUS_SUCCESS => $historySuccess * self::SECONDS_IN_MINUTE,
            Schedule::STATUS_MISSED => $historyFailure * self::SECONDS_IN_MINUTE,
            Schedule::STATUS_ERROR => $historyFailure * self::SECONDS_IN_MINUTE
        );

        $now = time();
        /** @var Schedule $record */
        foreach ($history as $record) {
            if (strtotime($record->getExecutedAt()) < $now - $historyLifetimes[$record->getStatus()]) {
                $record->delete();
            }
        }

        // save time history cleanup was ran with no expiration
        $this->_cache->save(time(), self::CACHE_KEY_LAST_HISTORY_CLEANUP_AT . $groupId, array('crontab'), null);

        return $this;
    }

    /**
     * @param array $jobConfig
     * @return mixed
     */
    protected function getConfigSchedule($jobConfig)
    {
        $cronExpr = $this->_scopeConfig->getValue(
            $jobConfig['config_path'],
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $cronExpr;
    }

    /**
     * @param string $jobCode
     * @param string $cronExpression
     * @param int $timeInterval
     * @param array $exists
     * @return void
     */
    protected function saveSchedule($jobCode, $cronExpression, $timeInterval, $exists)
    {
        $currentTime = time();
        $timeAhead = $currentTime + $timeInterval;
        for ($time = $currentTime; $time < $timeAhead; $time += self::SECONDS_IN_MINUTE) {
            $ts = strftime('%Y-%m-%d %H:%M:00', $time);
            if (!empty($exists[$jobCode . '/' . $ts])) {
                // already scheduled
                continue;
            }

            $schedule = $this->generateSchedule($jobCode, $cronExpression, $time);
            if ($schedule->trySchedule()) {
                // time matches cron expression
                $schedule->save();
            }
        }
    }

    /**
     * @param string $jobCode
     * @param string $cronExpression
     * @param int $time
     * @return Schedule
     */
    protected function generateSchedule($jobCode, $cronExpression, $time)
    {
        $schedule = $this->_scheduleFactory->create()
            ->setCronExpr($cronExpression)
            ->setJobCode($jobCode)
            ->setStatus(Schedule::STATUS_PENDING)
            ->setCreatedAt(strftime('%Y-%m-%d %H:%M:%S', time()))
            ->setScheduledAt(strftime('%Y-%m-%d %H:%M', $time));

        return $schedule;
    }

    /**
     * @param string $groupId
     * @return int
     */
    protected function getScheduleTimeInterval($groupId)
    {
        $scheduleAheadFor = (int)$this->_scopeConfig->getValue(
            'system/cron/' . $groupId . '/' . self::XML_PATH_SCHEDULE_AHEAD_FOR,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $scheduleAheadFor = $scheduleAheadFor * self::SECONDS_IN_MINUTE;

        return $scheduleAheadFor;
    }
}
