<?php

namespace MichaelJJames\UDFMailchimp;

// Silverstripe
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropDownField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Flushable;

// Mailchimp
use \DrewM\MailChimp\MailChimp;

class UDFMailChimpField extends EditableFormField implements Flushable
{

    private static $singular_name = 'Mailchimp Opt-in Field';

    private static $table_name = 'UDFMailChimpField';

    private static $has_placeholder = false;

    private static $mailchimp_key = false;

    private static $db = [
        'ListID' => 'Varchar(255)'
    ];

    public static function flush() 
    {
        Injector::inst()->get(CacheInterface::class . '.myCache')->clear();
    }

    public function getFormField()
    {
        $field = CheckboxField::create($this->Name, $this->EscapedTitle, $this->Default);
        return $field;
    }

    public function getApiKey() 
    {
        if (!$key = $this->config()->get('mailchimp_key')) {
            throw new \RuntimeException('mailchimp_key must be set');
        }
        return $key;
    }

    public function getLists() 
    {

        $cache = Injector::inst()->get(CacheInterface::class . '.myCache');

        if($cache->has('.myCache')) {

            $items = $cache->get('.myCache');

        }else{

            $MailChimp = new Mailchimp($this->getApiKey());

            $result = $MailChimp->get('lists');

            $items = new ArrayList();

            foreach($result['lists'] as $listItem) {
                $items->push(new ArrayData(array(
                    'ID' => $listItem['id'],
                    'Title' => $listItem['name']
                )));
            }

            $cache->set('.myCache', $items);

        }

        return $items;
    
    }

    public function getCMSFields() 
    {

        $fields = parent::getCMSFields();

        $field = DropDownField::create('ListID', 'Mailchimp List', $this->getLists()->map('ID', 'Title'))->setEmptyString('(Select one)');

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

            $MailChimp = new Mailchimp($this->getApiKey());

            $list_id = $this->ListID;

            $result = $MailChimp->post("lists/". $list_id ."/members", [
                'email_address' => $emailAddress,
                'status' => 'subscribed',
            ]);

        }
    
    }

}