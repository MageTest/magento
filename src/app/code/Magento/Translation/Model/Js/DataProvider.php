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

namespace Magento\Translation\Model\Js;

class DataProvider implements DataProviderInterface
{
    /**
     * Get translation data
     *
     * @return string[]
     */
    public function getData()
    {
        return array(
            'Complete' => __('Complete'),
            'Upload Security Error' => __('Upload Security Error'),
            'Upload HTTP Error' => __('Upload HTTP Error'),
            'Upload I/O Error' => __('Upload I/O Error'),
            'SSL Error: Invalid or self-signed certificate' => __('SSL Error: Invalid or self-signed certificate'),
            'TB' => __('TB'),
            'GB' => __('GB'),
            'MB' => __('MB'),
            'kB' => __('kB'),
            'B' => __('B'),
            'Add Products' => __('Add Products'),
            'Add Products By SKU' => __('Add Products By SKU'),
            'Insert Widget...' => __('Insert Widget...'),
            'Please wait, loading...' => __('Please wait, loading...'),
            'HTML tags are not allowed' => __('HTML tags are not allowed'),
            'Please select an option.' => __('Please select an option.'),
            'This is a required field.' => __('This is a required field.'),
            'Please enter a valid number in this field.' => __('Please enter a valid number in this field.'),
            'The value is not within the specified range.' => __('The value is not within the specified range.'),
            'Please use numbers only in this field. Please avoid spaces or other characters such as dots or commas.' => __('Please use numbers only in this field. Please avoid spaces or other characters such as dots or commas.'),
            'Please use letters only (a-z or A-Z) in this field.' => __('Please use letters only (a-z or A-Z) in this field.'),
            'Please use only letters (a-z), numbers (0-9) or underscore(_) in this field, first character should be a letter.' => __('Please use only letters (a-z), numbers (0-9) or underscore(_) in this field, first character should be a letter.'),
            'Please use only letters (a-z or A-Z) or numbers (0-9) only in this field. No spaces or other characters are allowed.' => __('Please use only letters (a-z or A-Z) or numbers (0-9) only in this field. No spaces or other characters are allowed.'),
            'Please use only letters (a-z or A-Z) or numbers (0-9) or spaces and # only in this field.' => __('Please use only letters (a-z or A-Z) or numbers (0-9) or spaces and # only in this field.'),
            'Please enter a valid fax number. For example (123) 456-7890 or 123-456-7890.' => __('Please enter a valid fax number. For example (123) 456-7890 or 123-456-7890.'),
            'Please enter a valid date.' => __('Please enter a valid date.'),
            'The From Date value should be less than or equal to the To Date value.' => __('The From Date value should be less than or equal to the To Date value.'),
            'Please enter a valid email address. For example johndoe@domain.com.' => __('Please enter a valid email address. For example johndoe@domain.com.'),
            'Please use only visible characters and spaces.' => __('Please use only visible characters and spaces.'),
            'Please enter 6 or more characters. Leading or trailing spaces will be ignored.' => __('Please enter 6 or more characters. Leading or trailing spaces will be ignored.'),
            'Please enter 7 or more characters. Password should contain both numeric and alphabetic characters.' => __('Please enter 7 or more characters. Password should contain both numeric and alphabetic characters.'),
            'Please make sure your passwords match.' => __('Please make sure your passwords match.'),
            'Please enter a valid URL. Protocol is required (http://, https:// or ftp://)' => __('Please enter a valid URL. Protocol is required (http://, https:// or ftp://)'),
            'Please enter a valid URL Key. For example "example-page", "example-page.html" or "anotherlevel/example-page".' => __('Please enter a valid URL Key. For example "example-page", "example-page.html" or "anotherlevel/example-page".'),
            'Please enter a valid XML-identifier. For example something_1, block5, id-4.' => __('Please enter a valid XML-identifier. For example something_1, block5, id-4.'),
            'Please enter a valid social security number. For example 123-45-6789.' => __('Please enter a valid social security number. For example 123-45-6789.'),
            'Please enter a valid zip code. For example 90602 or 90602-1234.' => __('Please enter a valid zip code. For example 90602 or 90602-1234.'),
            'Please enter a valid zip code.' => __('Please enter a valid zip code.'),
            'Please use this date format: dd/mm/yyyy. For example 17/03/2006 for the 17th of March, 2006.' => __('Please use this date format: dd/mm/yyyy. For example 17/03/2006 for the 17th of March, 2006.'),
            'Please select one of the above options.' => __('Please select one of the above options.'),
            'Please select one of the options.' => __('Please select one of the options.'),
            'Please select State/Province.' => __('Please select State/Province.'),
            'Please enter a number greater than 0 in this field.' => __('Please enter a number greater than 0 in this field.'),
            'Please enter a number 0 or greater in this field.' => __('Please enter a number 0 or greater in this field.'),
            'Please enter a valid credit card number.' => __('Please enter a valid credit card number.'),
            'Credit card number does not match credit card type.' => __('Credit card number does not match credit card type.'),
            'Card type does not match credit card number.' => __('Card type does not match credit card number.'),
            'Incorrect credit card expiration date.' => __('Incorrect credit card expiration date.'),
            'Please enter a valid credit card verification number.' => __('Please enter a valid credit card verification number.'),
            'Please use only letters (a-z or A-Z), numbers (0-9) or underscore(_) in this field, first character should be a letter.' => __('Please use only letters (a-z or A-Z), numbers (0-9) or underscore(_) in this field, first character should be a letter.'),
            'Please input a valid CSS-length. For example 100px or 77pt or 20em or .5ex or 50%.' => __('Please input a valid CSS-length. For example 100px or 77pt or 20em or .5ex or 50%.'),
            'Text length does not satisfy specified text range.' => __('Text length does not satisfy specified text range.'),
            'Please enter a number lower than 100.' => __('Please enter a number lower than 100.'),
            'Please select a file' => __('Please select a file'),
            'Please enter issue number or start date for switch/solo card type.' => __('Please enter issue number or start date for switch/solo card type.'),
            'This date is a required value.' => __('This date is a required value.'),
            'Please enter a valid day (1-%1).' => __('Please enter a valid day (1-%1).'),
            'Please enter a valid month (1-12).' => __('Please enter a valid month (1-12).'),
            'Please enter a valid year (1900-%1).' => __('Please enter a valid year (1900-%1).'),
            'Please enter a valid full date' => __('Please enter a valid full date'),
            'Allow' => __('Allow'),
            'Activate' => __('Activate'),
            'Reauthorize' => __('Reauthorize'),
            'Cancel' => __('Cancel'),
            'Done' => __('Done'),
            'Save' => __('Save'),
            'File extension not known or unsupported type.' => __('File extension not known or unsupported type.'),
            'Configure Product' => __('Configure Product'),
            'OK' => __('OK'),
            'Gift Options for ' => __('Gift Options for '),
            'New Option' => __('New Option'),
            'Add Products to New Option' => __('Add Products to New Option'),
            'Add Products to Option "%1"' => __('Add Products to Option "%1"'),
            'Add Selected Products' => __('Add Selected Products'),
            'Select type of option.' => __('Select type of option.'),
            'Please add rows to option.' => __('Please add rows to option.'),
            'Select Product' => __('Select Product'),
            'Import' => __('Import'),
            'Please select items.' => __('Please select items.'),
            'Add Products to Group' => __('Add Products to Group'),
            'start typing to search category' => __('start typing to search category'),
            'Choose existing category.' => __('Choose existing category.'),
            'Create Category' => __('Create Category'),
            'Sorry, there was an unknown error.' => __('Sorry, there was an unknown error.'),
            'Something went wrong while loading the theme.' => __('Something went wrong while loading the theme.'),
            'We don\'t recognize or support this file extension type.' => __('We don\'t recognize or support this file extension type.'),
            'Error' => __('Error'),
            'No stores were reassigned.' => __('No stores were reassigned.'),
            'Assign theme to your live store-view:' => __('Assign theme to your live store-view:'),
            'Default title' => __('Default title'),
            'The URL to assign stores is not defined.' => __('The URL to assign stores is not defined.'),
            'No' => __('No'),
            'Yes' => __('Yes'),
            'Some problem with revert action' => __('Some problem with revert action'),
            'Error: unknown error.' => __('Error: unknown error.'),
            'Some problem with save action' => __('Some problem with save action'),
            'Delete' => __('Delete'),
            'Folder' => __('Folder'),
            'Delete Folder' => __('Delete Folder'),
            'Are you sure you want to delete the folder named' => __('Are you sure you want to delete the folder named'),
            'Delete File' => __('Delete File'),
            'Method ' => __('Method '),
            'Please wait...' => __('Please wait...'),
            'Loading...' => __('Loading...'),
            'Translate' => __('Translate'),
            'Submit' => __('Submit'),
            'Close' => __('Close'),
            'Please enter a value less than or equal to %s.' => __('Please enter a value less than or equal to %s.'),
            'Please enter a value greater than or equal to %s.' => __('Please enter a value greater than or equal to %s.'),
            'Maximum length of this field must be equal or less than %1 symbols.' => __('Maximum length of this field must be equal or less than %1 symbols.'),
            'No records found.' => __('No records found.'),
            'Recent items' => __('Recent items'),
            'Show all...' => __('Show all...'),
            'Please enter a date in the past.' => __('Please enter a date in the past.'),
            'Please enter a date between %min and %max.' => __('Please enter a date between %min and %max.'),
            'Please choose to register or to checkout as a guest.' => __('Please choose to register or to checkout as a guest.'),
            'We are not able to ship to the selected shipping address. Please choose another address or edit the current address.' => __('We are not able to ship to the selected shipping address. Please choose another address or edit the current address.'),
            'Please specify a shipping method.' => __('Please specify a shipping method.'),
            'We can\'t complete your order because you don\'t have a payment method available.' => __('We can\'t complete your order because you don\'t have a payment method available.'),
            'Error happened while creating wishlist. Please try again later' => __('Error happened while creating wishlist. Please try again later'),
            'You must select items to move' => __('You must select items to move'),
            'You must select items to copy' => __('You must select items to copy'),
            'You are about to delete your wish list. This action cannot be undone. Are you sure you want to continue?' => __('You are about to delete your wish list. This action cannot be undone. Are you sure you want to continue?'),
            'Please specify payment method.' => __('Please specify payment method.'),
            'Are you sure you want to delete this address?' => __('Are you sure you want to delete this address?'),
            'Use gift registry shipping address' => __('Use gift registry shipping address'),
            'You can change the number of gift registry items on the Gift Registry Info page or directly in your cart, but not while in checkout.' => __('You can change the number of gift registry items on the Gift Registry Info page or directly in your cart, but not while in checkout.'),
            'No confirmation' => __('No confirmation'),
            'Sorry, something went wrong.' => __('Sorry, something went wrong.'),
            'Sorry, something went wrong. Please try again later.' => __('Sorry, something went wrong. Please try again later.'),
            'select all' => __('select all'),
            'unselect all' => __('unselect all'),
            'Please agree to all Terms and Conditions before placing the orders.' => __('Please agree to all Terms and Conditions before placing the orders.'),
            'Please choose to register or to checkout as a guest' => __('Please choose to register or to checkout as a guest'),
            'Your order cannot be completed at this time as there is no shipping methods available for it. Please make necessary changes in your shipping address.' => __('Your order cannot be completed at this time as there is no shipping methods available for it. Please make necessary changes in your shipping address.'),
            'Please specify shipping method.' => __('Please specify shipping method.'),
            'Your order cannot be completed at this time as there is no payment methods available for it.' => __('Your order cannot be completed at this time as there is no payment methods available for it.'),
            'Edit Order' => __('Edit Order'),
            'Ok' => __('Ok'),
            'Please specify at least one search term.' => __('Please specify at least one search term.'),
            'Create New Wish List' => __('Create New Wish List'),
            'Click Details for more required fields.' => __('Click Details for more required fields.'),
        );
    }
}
