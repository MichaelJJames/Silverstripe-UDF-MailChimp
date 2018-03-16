<?php

namespace MichaelJJames\UDFMailchimp;

// Silverstripe
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropDownField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;

// Mailchimp
use \DrewM\MailChimp\MailChimp;

class UDFMailChimpField extends EditableFormField 
{

    private static $singular_name = 'Mailchimp Opt-in Field';

    private static $table_name = 'UDFMailChimpField';

    private static $has_placeholder = false;

    private static $mailchimp_key = "";

    private static $db = [
        'ListID' => 'Varchar(255)'
    ];

    public function getFormField()
    {
        $field = CheckboxField::create($this->Name, $this->EscapedTitle, $this->Default);
        return $field;
    }

    public function getLists() 
    {

        $MailChimp = new MailChimp($this->config()->get('mailchimp_key'));

        $result = $MailChimp->get('lists');

        $items = new ArrayList();

        foreach($result['lists'] as $listItem) {
            $items->push(new ArrayData(array(
                'ID' => $listItem['id'],
                'Name' => $listItem['name']
            )));
        }

        return $items;
	
    }

    public function getCMSFields() 
    {

        $fields = parent::getCMSFields();

        $field = DropDownField::create('ListID', 'Mailchimp List', $this->getLists()->map('ID', 'Name'))->setEmptyString('(Select one)');

        $fields->addFieldToTab('Root.Main', $field);

        return $fields;

    }

    public function getValueFromData($data) 
    {

        $emailAddress = '';
        $subscribe = false;
        $udfmcfield = $this->getFormField()->name;

        foreach($data as $name => $fields) {

            if($name == $udfmcfield) {

                if($fields == 1) {

                    $subscribe = 1;

                }

            }

            if (filter_var($fields, FILTER_VALIDATE_EMAIL)) {

                $emailAddress = $fields;
			
            }

        }

        if($subscribe) {

            $MailChimp = new MailChimp($this->config()->get('mailchimp_key'));

            $list_id = $this->ListID;

            $result = $MailChimp->post("lists/". $list_id ."/members", [
                'email_address' => $emailAddress,
                'status' => 'subscribed',
            ]);

        }
	
    }

}