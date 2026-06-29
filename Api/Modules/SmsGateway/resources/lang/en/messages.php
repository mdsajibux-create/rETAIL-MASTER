<?php

// lang/en/messages.php

return [
    'token.invalid' => 'Token is invalid',
    'token.verified' => 'Token is verified',

    'send_success' => ':name sent successfully.',
    'send_first' => 'Please send :name first!',
    'send_failed' => 'Failed to send :name!',
    'verification_success' => ':name is verified successfully.',
    'verification_failed' => 'Failed to verify :name!',
    'expired' => ':name has been expired!',

    'email.verify.success' => 'Email verified successfully',
    'email.resend.failed' => 'Email resend failed',
    'email.resend.success' => 'Email resend successfully',
    'old_password_invalid' => 'Old password is invalid',
    'suspended' => ':name account suspended!',
    'user_invalid' => 'User is not :user !',

    'something_went_wrong' => 'Something went wrong',

    'save_success' => ':name Successfully added!',
    'save_failed' => 'Failed to save new :name',

    'update_success' => ':name Successfully updated!',
    'update_failed' => 'Failed to update :name',

    'delete_success' => 'Record has been successfully deleted!',
    'delete_failed' => 'Record Failed to Delete',

    'status_change_success' => 'Status has been successfully changed!',
    'status_change_failed' => 'Failed to Delete :name',

    'login_success' => ':name logged in successfully!',
    'login_failed' => ':name log in failed!',

    'registration_success' => ':name registered successfully!',
    'registration_failed' => ':name registration failed!',

    'validation_success' => ':name validation successfully!',
    'validation_failed' => ':name validation failed!',

    'error' => 'Something went wrong!',
    'authorization_invalid' => 'Not authorized!',
    'data_found' => 'Data was found successfully!',
    'data_not_found' => 'Data was not found!',
    'access_denied' => 'Access denied!',

    'ticket.resolved' => 'Ticket has been resolved!',
    'ticket.closed' => 'Ticket has been closed!',
    'ticket_does_not_belongs_to_this_store' => 'Ticket does not belongs to this store!',
    'ticket_does_not_belongs_to_this_customer' => 'Ticket does not belongs to this customer!',

    'customer.not.found' => 'This customer email doesn\'t exists!',
    'wrong_credential' => 'Wrong credential!',
    'support_ticket.message.sent' => 'Support ticket has been sent!',

    'store.doesnt.belongs.to.seller' => 'Store doesn\'t belongs to this seller!',
    'staff_doesnt_belongs_to_seller' => 'Staff doesn\'t belongs to this seller!',
    'staff_not_assign_to_stores' => 'Staff is not assigned to any store yet!',
    'role_can\'t_be_deleted' => ':reason Role cannot be deleted!',
    'role_can\'t_be_edited' => ':reason Role cannot be edited!',
    'staff_can\'t_be_modified' => ':reason Role cannot be :action!',

    'store_not_found' => 'Store not found or access denied',
    'store_subscription_invalid_type' => 'Invalid subscription type for the store',
    'store_subscription_no_active_not_found' => 'No active subscription found for the store.',
    'store_subscription_not_found' => 'subscription not found.',
    'store_subscription_insufficient_balance' => 'Insufficient wallet balance. Please deposit funds to continue.',

    'default.address' => 'Default address can\'t be deleted!',
    'invalid.address' => 'Invalid address!',

    'approve.success' => ':name approved successfully!',
    'approve.failed' => ':name approved failed!',

    'reject.success' => ':name rejected successfully!',
    'reject.failed' => ':name rejection failed!',
    'exists' => ':name already exists!',

    'request_success' => ':name requested successfully!',
    'request_failed' => ':name request failed!',

    'account_deactivate_successful' => 'Your account has been deactivated!',
    'account_activate_successful' => 'Your account has been activated!',
    'account_deactivate_failed' => 'Something went wrong while deactivating your account!',
    'account_already_activated' => 'Your account is already activated!',
    'account_already_deactivated' => 'Your account has been already deactivated!',

    'account_delete_successful' => 'Your account has been deleted!',
    'account_delete_failed' => 'Something went wrong while deleting your account!',

    'account_activity_notification_update_success' => 'Activity notification preference updated successfully!',
    'account_marketing_notification_update_success' => 'Marketing email preference updated successfully!',

    'password_update_successful' => 'Password has been successfully updated!',
    'password_update_failed' => 'Password update failed!',

    'coupon_not_found' => 'Coupon not found.',
    'coupon_does_not_belong' => 'Coupon does not belong to this user.',
    'coupon_inactive' => 'Coupon is not yet active.',
    'coupon_expired' => 'Coupon has expired.',
    'coupon_limit_reached' => 'Coupon usage limit reached.',
    'coupon_applied' => 'Coupon applied successfully!.',
    'coupon_min_order_amount' => 'Minimum order amount is :amount.',

    'settings_not_created_yet' => 'Settings not created yet!',

    'deliveryman_order_request_accept_successful' => 'Order request accepted successfully!',
    'deliveryman_order_request_ignore_successful' => 'Order request ignored successfully!',
    'deliveryman_order_already_taken' => 'This order has been already confirmed by other deliveryman!',
    'deliveryman_order_already_accepted' => 'This order has been already accepted by you!',
    'deliveryman_order_already_ignored' => 'This order has been already ignored by you!',
    'deliveryman_assign_successful' => 'Deliveryman assigned successfully!',
    'deliveryman_assign_failed' => 'Deliveryman assign failed!',
    'deliveryman_active_order_exists' => 'You have active orders! Can\'t deactivate or delete your account',
    'deliveryman_can_not_be_assigned' => 'Deliveryman can\'t be assigned!',
    'deliveryman_has_active_orders' => 'You have active orders! Can\'t change available status.',


    'customer_product_query_submitted_successful' => 'Your query has been submitted successfully!',
    'customer_product_query_submitted_failed' => 'Your query submission failed!',

    'reply_success' => 'Replied successfully!',

    'order_cancel_successful' => 'Order cancelled successfully!',
    'order_cancel_failed' => 'Order cancellation failed!',
    'order_already_cancelled' => 'Order has been already cancelled!',
    'order_already_delivered' => 'Order has been already delivered!',
    'order_status_not_changeable' => 'This order status cannot be changed!',
    'order_does_not_belong_to_seller' => 'This order does not belong to this seller!',
    'order_does_not_belong_to_customer' => 'This order does not belong to this customer!',
    'order_does_not_belong_to_deliveryman' => 'This order does not belong to this deliveryman!',
    'order_refund_success' => 'Order refunded successfully!',
    'order_refund_failed' => 'Order refund failed!',
    'order_refund_request_success' => 'Order refund requested successfully!',
    'order_refund_request_failed' => 'Order refund request failed!',
    'order_already_request_for_refund' => 'Order already requested for refund!',
    'order_is_not_delivered' => 'Order is not delivered yet!',
    'order_delivered_success' => 'Order delivered successfully!',
    'order_already_cancelled_or_ignored_or_delivered' => 'This order is already either cancelled or ignored or delivered!',
    'order_is_not_accepted' => 'This order is not accepted yet!',
    'order_is_not_cash_on_delivery' => 'This order is not cash on delivery!',
    'order_is_not_shipped' => 'This order is not shipped yet!',
    'order_confirmation_store' => 'Order delivery confirmation setting is set to store!',
    'received_amount_can\'t_be_greater' => 'The received amount is greater than collected amount!',
    'total_amount_exceed' => 'Total collected amount exceeds total order amount! Remaining amount is :remainingAmount!',
    'order_accept_limit_reached' => 'You have running orders. Can\'t accept more than :limit orders at a time!',

    'can\'t_modify' => 'You can\'t modify this :name!',

    'commission_option_is_not_available' => 'Commission option is not available!',
    'subscription_option_is_not_available' => 'Subscription option is not available!',

    'out_of_stock' => ':variant is out of stock!',

    'currently_not_available' => 'Sorry! We are currently not available!',

    'wallet_not_found' => 'Wallet not found!',
    'insufficient_limit' => 'Insufficient limit!',
    'product_featured_added_successfully' => 'Product featured added successfully!',
    'product_featured_removed_successfully' => 'Product featured removed successfully!',
    'already_featured' => 'This :name is already featured!',

    'available_status_inactive' => 'Available status is inactive!',
    'setting_disabled' => ':name settings is disabled'

];


