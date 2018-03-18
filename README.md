# Silverstripe UDF MailChimp

Adds a new field to UDF to allow you to let users choose to subscribe to a mailchimp list you have choose in the admin

# Requirements

SilverStripe SS4

[drewm/mailchimp-api](https://github.com/drewm/mailchimp-api)

# Installation
```
composer require michaeljjames/silverstripe-udf-mailchimp
```
In your /mysite/_config/mysite.yml

```YML
MichaelJJames\UDFMailchimp\UDFMailChimpField:
  mailchimp_key: YOUR KEY HERE
```
# MailChimp Lists

If you add a new mailchimp list and it doesn't show in the CMS you may want to ?flush=1 as the lists are cached to save making the request on each page load.