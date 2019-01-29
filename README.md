[![Build Status](https://travis-ci.org/wmde/fundraising-address-change.svg?branch=master)](https://travis-ci.org/wmde/fundraising-address-change)
[![Code Coverage](https://scrutinizer-ci.com/g/wmde/fundraising-address-change/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/wmde/fundraising-address-change/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/wmde/fundraising-address-change/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/wmde/fundraising-address-change/?branch=master)


# Fundraising Address Change

Bounded Context for the Wikimedia Deutschland fundraising address change (sub-)domain. 

* When exporting address-related records (donation, membership, subscription), each record has a random UUID that can be used to reference address changes to that record in the future.
* Users can get links to the address change page, each link being secured with the UUID that identifies the address change.
* Whenever an address changes, its UUID is regenerated.
* Whenever an address changes, it is marked as modified (by tracking creation vs modification date). Only the last modification date is recorded.
* Address changes are exported as individual records, with current and previous UUID.

