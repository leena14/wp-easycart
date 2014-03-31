﻿;
ALTER TABLE ec_order ADD `creditcard_digits` VARCHAR(4) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'If credit card checkout is used, saves the last four digits here.';
ALTER TABLE ec_product ADD `is_subscription_item` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Makes this product a subscription product which is purchased individually.';
ALTER TABLE ec_product ADD `subscription_bill_length` INTEGER(11) NOT NULL DEFAULT '1' COMMENT 'Number of the period times to charge the customer, e.g. 3 paired with month is charge once every 3 months.';
ALTER TABLE ec_product ADD `subscription_bill_period` VARCHAR(20) COLLATE utf8_general_ci NOT NULL DEFAULT 'M' COMMENT 'The period of the subscription, valid values are: D, W, M, Y.';
ALTER TABLE ec_setting ADD `ups_conversion_rate` FLOAT(9,3) NOT NULL DEFAULT '1.000' COMMENT 'Converts the returned pricing.';
ALTER TABLE ec_setting ADD `fedex_conversion_rate` FLOAT(9,3) NOT NULL DEFAULT '1.000' COMMENT 'Converts the returned pricing.';
ALTER TABLE ec_setting ADD `fedex_test_account` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'If true, FedEx account is a test account.';
ALTER TABLE ec_shippingrate ADD `is_quantity_based` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'If selected, this rate is for quantity based shipping.';
ALTER TABLE ec_menulevel1 MODIFY `name` VARCHAR( 1024 ) NOT NULL;
ALTER TABLE ec_menulevel2 MODIFY `name` VARCHAR( 1024 ) NOT NULL;
ALTER TABLE ec_menulevel3 MODIFY `name` VARCHAR( 1024 ) NOT NULL;
CREATE TABLE IF NOT EXISTS `ec_subscription` (
  `subscription_id` INTEGER(11) NOT NULL AUTO_INCREMENT COMMENT
   'Unique ID for this table',
  `subscription_type` VARCHAR(125) COLLATE utf8_general_ci NOT NULL DEFAULT 'paypal' COMMENT
   'Type of subscription, e.g. paypal',
  `subscription_status` VARCHAR(125) COLLATE utf8_general_ci NOT NULL DEFAULT 'Active' COMMENT
   'Status of the subscription, Active or Canceled for example.',
  `title` VARCHAR(510) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT
   'Title of the product purchased.',
  `model_number` VARCHAR(510) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT
   'Model number of the product purchased.',
  `price` double(21,3) NOT NULL DEFAULT '0.000' COMMENT
   'Price of the product per period',
  `payment_length` int(11) NOT NULL DEFAULT '1' COMMENT
   'Length of time between payments, e.g. 3 months, represented by 3 in this field.',
  `payment_period` VARCHAR(20) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT
   'Period of the payment, day, week, month of year, represented as D, W, M, or Y in this field.',
  `start_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT
   'Date that this payment was submitted to start the subscription.',
  `last_payment_date` VARCHAR(510) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT
   'The last date that this subscription was paid for.',
  `next_payment_date` VARCHAR(510) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT
   'The next payment due date.',
  `email` VARCHAR(510) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT
   'Email entered by the user while purchasing the subscription',
  `first_name` VARCHAR(155) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT
   'First name of the customer.',
  `last_name` VARCHAR(155) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT
   'Last name of the customer.',
  `user_country` VARCHAR(20) COLLATE utf8_general_ci NOT NULL DEFAULT 'US' COMMENT
   'Customer country of residence',
  `number_payments_completed` int(11) NOT NULL DEFAULT '1' COMMENT
   'The number of times this subscription has been paid for.',
  `paypal_txn_id` VARCHAR(510) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT
   'Initial transaction ID from PayPal',
  `paypal_txn_type` VARCHAR(510) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT
   'Initial transaction type from PayPal',
  `paypal_subscr_id` VARCHAR(510) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT
   'Initial subscription ID from PayPal used to track the subscription when updated or canceled.',
  `paypal_username` VARCHAR(510) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT
   'Username assigned to this subscription by PayPal.',
  `paypal_password` VARCHAR(510) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT
   'Password assigned to this subscription by PayPal.',
  PRIMARY KEY (`subscription_id`)
) ENGINE=MyISAM 
AUTO_INCREMENT=1 CHARACTER SET'utf8' COLLATE 'utf8_general_ci';
ALTER TABLE ec_product ADD `width` DOUBLE(15,3) NOT NULL DEFAULT '1.000' COMMENT 'Width of the product in the default shipping unit.';
ALTER TABLE ec_product ADD `height` DOUBLE(15,3) NOT NULL DEFAULT '1.000' COMMENT 'Height of the product in the default shipping unit.';
ALTER TABLE ec_product ADD `length` DOUBLE(15,3) NOT NULL DEFAULT '1.000' COMMENT 'Length of the product in the default shipping unit.';
ALTER TABLE ec_product ADD `trial_period_days` INTEGER(11) NOT NULL DEFAULT '0' COMMENT 'Length of subscription trial period in days.';
ALTER TABLE ec_product ADD `stripe_plan_added` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Has this subscription product been added to Stripe.';
ALTER TABLE ec_product ADD `subscription_plan_id` INTEGER(11) NOT NULL DEFAULT '0' COMMENT 'Used to group the subscriptions in a membership plan used for upgrade.';
ALTER TABLE ec_product ADD `allow_multiple_subscription_purchases` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Should this item be able to be purchased multiple times.';
ALTER TABLE ec_setting ADD `fraktjakt_customer_id` VARCHAR(64) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'Fraktjakt Customer ID.';
ALTER TABLE ec_setting ADD `fraktjakt_login_key` VARCHAR(64) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'Fraktjakt Login Key.';
ALTER TABLE ec_setting ADD `fraktjakt_conversion_rate` DOUBLE(15,3) NOT NULL DEFAULT '1.000' COMMENT 'The conversion rate between your base currency and SEK.';
ALTER TABLE ec_setting ADD `fraktjakt_test_mode` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Use test mode for Fraktjakt.';
ALTER TABLE ec_order ADD `fraktjakt_order_id` VARCHAR(20) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'Order ID for the Fraktjakt shipment.';
ALTER TABLE ec_order ADD `fraktjakt_shipment_id` VARCHAR(20) COLLATE utf8_general_ci DEFAULT '' COMMENT 'Shipment ID for the Fraktjakt shipment.';
ALTER TABLE ec_order ADD `stripe_charge_id` VARCHAR(128) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'Stripe Charge ID if Stripe used.';
ALTER TABLE ec_order ADD `subscription_id` INTEGER(11) NOT NULL DEFAULT '0' COMMENT 'Subscription ID from the ec_subscription table if order was a subscription order.';
ALTER TABLE ec_promocode ADD `max_redemptions` INTEGER(11) NOT NULL DEFAULT '999' COMMENT 'The maximum number of times you can use this promotion code.';
ALTER TABLE ec_promocode ADD `times_redeemed` INTEGER(11) NOT NULL DEFAULT '0' COMMENT 'This is the number of times this coupon has been redeemed.';
ALTER TABLE ec_user ADD `stripe_customer_id` VARCHAR(128) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'Stripe Customer ID if subscription created with Stripe.';
ALTER TABLE ec_subscription ADD `stripe_subscription_id` VARCHAR(128) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'If subscription created with Stripe, Stripe ID here.';
ALTER TABLE ec_subscription MODIFY `last_payment_date` VARCHAR(510) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'Last payment made.';
CREATE TABLE IF NOT EXISTS `ec_subscription_plan` (
  `subscription_plan_id` INTEGER(11) NOT NULL AUTO_INCREMENT COMMENT
   'Unique ID for a Subscription Plan.',
  `plan_title` VARCHAR(512) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT
   'Title to describe the plan of connecting subscriptions.',
  `can_downgrade` TINYINT(1) NOT NULL DEFAULT '0' COMMENT
   'Can a customer automatically downgrade their subscription plan.',
  PRIMARY KEY (`subscription_plan_id`)
) ENGINE=MyISAM 
AUTO_INCREMENT=0 CHARACTER SET'utf8' COLLATE
 'utf8_general_ci'
COMMENT=''
;
ALTER TABLE ec_order ADD `refund_total` FLOAT(15,3) NOT NULL DEFAULT 0.000 COMMENT 'Amount of the order that has been refunded.';
ALTER TABLE ec_product ADD `is_preorder` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Makes this product a preorder product, allowing for an authorization of a card without capturing at this time';
ALTER TABLE ec_product ADD `membership_page` VARCHAR(512) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'Optional link to a membership content page to be displayed after subscription purchased.';
ALTER TABLE ec_subscription ADD `user_id` INT(11) DEFAULT NOT NULL DEFAULT 0 COMMENT 'User ID of the subscription owner.';
ALTER TABLE ec_subscription ADD `product_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'Product ID of the subscription to connect to.';
ALTER TABLE ec_user ADD `default_card_type` VARCHAR(20) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'Used for subscription display of where billed to.';
ALTER TABLE ec_user ADD `default_card_last4` VARCHAR(8) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'Used for subscription display of where billed to.';
ALTER TABLE ec_shippingrate ADD `is_percentage_based` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'If selected, this rate is for percentage based shipping.';
ALTER TABLE ec_state ADD `group_sta` VARCHAR(100) COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'Option to group states in the state dropdown by a group title';
CREATE TABLE IF NOT EXISTS `ec_webhook` (
  `webhook_id` VARCHAR(128) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The unique indentifier for the webhook table, used by Stripe.',
  `webhook_type` VARCHAR(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'The type of webhook called.',
  `webhook_data` BLOB COMMENT 'The data returned from stripe in this webhook call.',
  PRIMARY KEY (`webhook_id`),
  UNIQUE KEY `webhook_id` (`webhook_id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' DEFAULT CHARSET=utf8 PACK_KEYS=0;