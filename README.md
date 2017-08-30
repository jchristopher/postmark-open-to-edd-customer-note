This is a WordPress plugin. [Official download available on wordpress.org](http://wordpress.org/extend/plugins/postmark-open-to-edd-customer-note/).

# Postmark Open to EDD Customer Note

Add a Customer Note when a Postmark-delivered email has been opened by an EDD Customer

## Description

Sets up a REST endpoint to be used by Postmark's Open Webhook to log opens of Postmark-delivered (via Postmark Approved) messages as Easy Digital Downloads (EDD) Customer Notes

**Requires** both [Postmark Approved](https://wordpress.org/plugins/postmark-approved-wordpress-plugin/) (with an accurate Server API key) and [Easy Digital Downloads](https://wordpress.org/plugins/easy-digital-downloads/) (2.6+) to be installed and active

## Installation

Install this plugin as you would any other WordPress plugin. There is no settings screen.

Your Postmark API key is pulled from [Postmark Approved](https://wordpress.org/plugins/postmark-approved-wordpress-plugin/) (or you can use the `pmeddcn_postmark_api_key` filter to populate your Postmark API key). EDD 2.6 is also required to be active.

### Postmark setup

**Note:** ensure this plugin is active before proceeding

This plugin makes use of Postmark's `Opens Webhook` by setting up a REST endpoint for it to hit. The default endpoint is:

`https://yoursite.com/wp-json/pmeddcn/v1/open/`

(You can customize both the route and the slug with the `pmeddcn_rest_route` and `pmeddcn_rest_slug` filters, respectively)

You must add the endpoint to your applicable Postmark Server:

1. Log in to your Postmark account
1. Click the **Servers** tab, and select the applicable server
1. Click the **Settings** tab for that Server
1. Click the **Outbound Settings** tab
1. Populate the **Opens webhook** field with the endpoint

![Postmark Opens webhook](https://cdn-sec.droplr.net/files/acc_2093/FORkeB?response-content-disposition=inline;%20filename=2017-08-30%2520at%252012.49%2520PM.png&Expires=1504112256&Signature=LnPSuJrfuOsLKVsR6Ns3pU6y7-r1st9KDOdr0wVhyEfUVXmSB3oCT7hi-07dAgg0U93nGoWhhO5bdUhMmGNyBP-YPmpud5~t-li7DE8uogV8t5RQhzGj9tEkawaXz73DWHF22yKpgFfZo2wV0V-2tP7wdICxS-JvHxbxD2hAKvM_&Key-Pair-Id=APKAJTEIOJM3LSMN33SA)

Click the `Check` button to verify a `200` response.

## Viewing opens

When Postmark-delievered emails are read by your EDD Customers, a Note will be added to their Customer record indicating as such.

![EDD Customer Notes](https://cdn-pro.droplr.net/files/acc_2093/LMJhDV)
