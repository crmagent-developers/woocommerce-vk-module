<?php

if ( ! class_exists( 'VKExceptionMapper' ) ) {

	/**
	 * Class VKExceptionMapper
	 */
	class VKExceptionMapper {

		/**
		 * @param VkApiError $error
		 *
		 * @return Exception
		 */
		public static function parse( VkApiError $error ) {

			if ( ! class_exists( 'VKApiException' ) ) {
				include_once( __DIR__ . '/class-wc-vk-api-exception.php' );
			}

			switch ( $error->getErrorCode() ) {
				case 1:
					return new \VKApiException( 1, 'Unknown error occurred', $error );
				case 2:
					return new \VKApiException( 2, 'Application is disabled. Enable your application or use test mode', $error );
				case 3:
					return new \VKApiException( 3, 'Unknown method passed', $error );
				case 4:
					return new \VKApiException( 4, 'Incorrect signature', $error );
				case 5:
					return new \VKApiException( 5, 'User authorization failed', $error );
				case 6:
					return new \VKApiException( 6, 'Too many requests per second', $error );
				case 7:
					return new \VKApiException( 7, 'Permission to perform this action is denied', $error );
				case 8:
					return new \VKApiException( 8, 'Invalid request', $error );
				case 9:
					return new \VKApiException( 9, 'Flood control', $error );
				case 10:
					return new \VKApiException( 10, 'Internal server error', $error );
				case 11:
					return new \VKApiException( 11, 'In test mode application should be disabled or user should be authorized', $error );
				case 12:
					return new \VKApiException( 12, 'Unable to compile code', $error );
				case 13:
					return new \VKApiException( 13, 'Runtime error occurred during code invocation', $error );
				case 14:
					return new \VKApiException( 14, 'Captcha needed', $error );
				case 15:
					return new \VKApiException( 15, 'Access denied', $error );
				case 16:
					return new \VKApiException( 16, 'HTTP authorization failed', $error );
				case 17:
					return new \VKApiException( 17, 'Validation required', $error );
				case 18:
					return new \VKApiException( 18, 'User was deleted or banned', $error );
				case 19:
					return new \VKApiException( 19, 'Content blocked', $error );
				case 20:
					return new \VKApiException( 20, 'Permission to perform this action is denied for non-standalone applications', $error );
				case 21:
					return new \VKApiException( 21, 'Permission to perform this action is allowed only for standalone and OpenAPI applications', $error );
				case 22:
					return new \VKApiException( 22, 'Upload error', $error );
				case 23:
					return new \VKApiException( 23, 'This method was disabled', $error );
				case 24:
					return new \VKApiException( 24, 'Confirmation required', $error );
				case 25:
					return new \VKApiException( 25, 'Token confirmation required', $error );
				case 27:
					return new \VKApiException( 27, 'Group authorization failed', $error );
				case 28:
					return new \VKApiException( 28, 'Application authorization failed', $error );
				case 29:
					return new \VKApiException( 29, 'Rate limit reached', $error );
				case 30:
					return new \VKApiException( 30, 'This profile is private', $error );
				case 100:
					return new \VKApiException( 100, 'One of the parameters specified was missing or invalid', $error );
				case 101:
					return new \VKApiException( 101, 'Invalid application API ID', $error );
				case 103:
					return new \VKApiException( 103, 'Out of limits', $error );
				case 104:
					return new \VKApiException( 104, 'Not found', $error );
				case 105:
					return new \VKApiException( 105, 'Couldn\'t save file', $error );
				case 106:
					return new \VKApiException( 106, 'Unable to process action', $error );
				case 113:
					return new \VKApiException( 113, 'Invalid user id', $error );
				case 114:
					return new \VKApiException( 114, 'Invalid album id', $error );
				case 118:
					return new \VKApiException( 118, 'Invalid server', $error );
				case 119:
					return new \VKApiException( 119, 'Invalid title', $error );
				case 121:
					return new \VKApiException( 121, 'Invalid hash', $error );
				case 122:
					return new \VKApiException( 122, 'Invalid photos', $error );
				case 125:
					return new \VKApiException( 125, 'Invalid group id', $error );
				case 129:
					return new \VKApiException( 129, 'Invalid photo', $error );
				case 140:
					return new \VKApiException( 140, 'Page not found', $error );
				case 141:
					return new \VKApiException( 141, 'Access to page denied', $error );
				case 146:
					return new \VKApiException( 146, 'The mobile number of the user is unknown', $error );
				case 147:
					return new \VKApiException( 147, 'Application has insufficient funds', $error );
				case 148:
					return new \VKApiException( 148, 'Access to the menu of the user denied', $error );
				case 150:
					return new \VKApiException( 150, 'Invalid timestamp', $error );
				case 171:
					return new \VKApiException( 171, 'Invalid list id', $error );
				case 173:
					return new \VKApiException( 173, 'Reached the maximum number of lists', $error );
				case 174:
					return new \VKApiException( 174, 'Cannot add user himself as friend', $error );
				case 175:
					return new \VKApiException( 175, 'Cannot add this user to friends as they have put you on their blacklist', $error );
				case 176:
					return new \VKApiException( 176, 'Cannot add this user to friends as you put him on blacklist', $error );
				case 177:
					return new \VKApiException( 177, 'Cannot add this user to friends as user not found', $error );
				case 180:
					return new \VKApiException( 180, 'Note not found', $error );
				case 182:
					return new \VKApiException( 182, 'You can\'t comment this note', $error );
				case 181:
					return new \VKApiException( 181, 'Access to note denied', $error );
				case 183:
					return new \VKApiException( 183, 'Access to comment denied', $error );
				case 200:
					return new \VKApiException( 200, 'Access denied', $error );
				case 201:
					return new \VKApiException( 201, 'Access denied', $error );
				case 203:
					return new \VKApiException( 203, 'Access to group denied', $error );
				case 204:
					return new \VKApiException( 204, 'Access denied', $error );
				case 205:
					return new \VKApiException( 205, 'Access denied', $error );
				case 210:
					return new \VKApiException( 210, 'Access to wall\'s post denied', $error );
				case 211:
					return new \VKApiException( 211, 'Access to wall\'s comment denied', $error );
				case 212:
					return new \VKApiException( 212, 'Access to post comments denied', $error );
				case 213:
					return new \VKApiException( 213, 'Access to status replies denied', $error );
				case 214:
					return new \VKApiException( 214, 'Access to adding post denied', $error );
				case 219:
					return new \VKApiException( 219, 'Advertisement post was recently added', $error );
				case 220:
					return new \VKApiException( 220, 'Too many recipients', $error );
				case 221:
					return new \VKApiException( 221, 'User disabled track name broadcast', $error );
				case 222:
					return new \VKApiException( 222, 'Hyperlinks are forbidden', $error );
				case 223:
					return new \VKApiException( 223, 'Too many replies', $error );
				case 224:
					return new \VKApiException( 224, 'Too many ads posts', $error );
				case 250:
					return new \VKApiException( 250, 'Access to poll denied', $error );
				case 251:
					return new \VKApiException( 251, 'Invalid poll id', $error );
				case 252:
					return new \VKApiException( 252, 'Invalid answer id', $error );
				case 253:
					return new \VKApiException( 253, 'Access denied, please vote first', $error );
				case 260:
					return new \VKApiException( 260, 'Access to the groups list is denied due to the user\'s privacy settings', $error );
				case 300:
					return new \VKApiException( 300, 'This album is full', $error );
				case 302:
					return new \VKApiException( 302, 'Albums number limit is reached', $error );
				case 500:
					return new \VKApiException( 500, 'Permission denied. You must enable votes processing in application settings', $error );
				case 503:
					return new \VKApiException( 503, 'Not enough votes', $error );
				case 600:
					return new \VKApiException( 600, 'Permission denied. You have no access to operations specified with given object(s)', $error );
				case 601:
					return new \VKApiException( 601, 'Permission denied. You have requested too many actions this day. Try later.', $error );
				case 603:
					return new \VKApiException( 603, 'Some ads error occured', $error );
				case 602:
					return new \VKApiException( 602, 'Some part of the request has not been completed', $error );
				case 629:
					return new \VKApiException( 629, 'Object deleted', $error );
				case 700:
					return new \VKApiException( 700, 'Cannot edit creator role', $error );
				case 701:
					return new \VKApiException( 701, 'User should be in club', $error );
				case 702:
					return new \VKApiException( 702, 'Too many officers in club', $error );
				case 703:
					return new \VKApiException( 703, 'You need to enable 2FA for this action', $error );
				case 704:
					return new \VKApiException( 704, 'User needs to enable 2FA for this action', $error );
				case 706:
					return new \VKApiException( 706, 'Too many addresses in club', $error );
				case 711:
					return new \VKApiException( 711, 'Application is not installed in community', $error );
				case 800:
					return new \VKApiException( 800, 'This video is already added', $error );
				case 801:
					return new \VKApiException( 801, 'Comments for this video are closed', $error );
				case 900:
					return new \VKApiException( 900, 'Can\'t send messages for users from blacklist', $error );
				case 901:
					return new \VKApiException( 901, 'Can\'t send messages for users without permission', $error );
				case 902:
					return new \VKApiException( 902, 'Can\'t send messages to this user due to their privacy settings', $error );
				case 907:
					return new \VKApiException( 907, 'Value of ts or pts is too old', $error );
				case 908:
					return new \VKApiException( 908, 'Value of ts or pts is too new', $error );
				case 909:
					return new \VKApiException( 909, 'Can\'t edit this message, because it\'s too old', $error );
				case 910:
					return new \VKApiException( 910, 'Can\'t sent this message, because it\'s too big', $error );
				case 911:
					return new \VKApiException( 911, 'Keyboard format is invalid', $error );
				case 912:
					return new \VKApiException( 912, 'This is a chat bot feature, change this status in settings', $error );
				case 913:
					return new \VKApiException( 913, 'Too many forwarded messages', $error );
				case 914:
					return new \VKApiException( 914, 'Message is too long', $error );
				case 917:
					return new \VKApiException( 917, 'You don\'t have access to this chat', $error );
				case 919:
					return new \VKApiException( 919, 'You can\'t see invite link for this chat', $error );
				case 920:
					return new \VKApiException( 920, 'Can\'t edit this kind of message', $error );
				case 921:
					return new \VKApiException( 921, 'Can\'t forward these messages', $error );
				case 924:
					return new \VKApiException( 924, 'Can\'t delete this message for everybody', $error );
				case 925:
					return new \VKApiException( 925, 'You are not admin of this chat', $error );
				case 927:
					return new \VKApiException( 927, 'Chat does not exist', $error );
				case 931:
					return new \VKApiException( 931, 'You can\'t change invite link for this chat', $error );
				case 932:
					return new \VKApiException( 932, 'Your community can\'t interact with this peer', $error );
				case 935:
					return new \VKApiException( 935, 'User not found in chat', $error );
				case 936:
					return new \VKApiException( 936, 'Contact not found', $error );
				case 939:
					return new \VKApiException( 939, 'Message request already send', $error );
				case 940:
					return new \VKApiException( 940, 'Too many posts in messages', $error );
				case 942:
					return new \VKApiException( 942, 'Cannot pin one-time story', $error );
				case 1000:
					return new \VKApiException( 1000, 'Invalid phone number', $error );
				case 1004:
					return new \VKApiException( 1004, 'This phone number is used by another user', $error );
				case 1105:
					return new \VKApiException( 1105, 'Too many auth attempts, try again later', $error );
				case 1112:
					return new \VKApiException( 1112, 'Processing.. Try later', $error );
				case 1150:
					return new \VKApiException( 1150, 'Invalid document id', $error );
				case 1151:
					return new \VKApiException( 1151, 'Access to document deleting is denied', $error );
				case 1152:
					return new \VKApiException( 1152, 'Invalid document title', $error );
				case 1153:
					return new \VKApiException( 1153, 'Access to document is denied', $error );
				case 1160:
					return new \VKApiException( 1160, 'Original photo was changed', $error );
				case 1170:
					return new \VKApiException( 1170, 'Too many feed lists', $error );
				case 1251:
					return new \VKApiException( 1251, 'This achievement is already unlocked', $error );
				case 1256:
					return new \VKApiException( 1256, 'Subscription not found', $error );
				case 1257:
					return new \VKApiException( 1257, 'Subscription is in invalid status', $error );
				case 1260:
					return new \VKApiException( 1260, 'Invalid screen name', $error );
				case 1310:
					return new \VKApiException( 1310, 'Catalog is not available for this user', $error );
				case 1311:
					return new \VKApiException( 1311, 'Catalog categories are not available for this user', $error );
				case 1400:
					return new \VKApiException( 1400, 'Too late for restore', $error );
				case 1401:
					return new \VKApiException( 1401, 'Comments for this market are closed', $error );
				case 1402:
					return new \VKApiException( 1402, 'Album not found', $error );
				case 1403:
					return new \VKApiException( 1403, 'Item not found', $error );
				case 1404:
					return new \VKApiException( 1404, 'Item already added to album', $error );
				case 1405:
					return new \VKApiException( 1405, 'Too many items', $error );
				case 1406:
					return new \VKApiException( 1406, 'Too many items in album', $error );
				case 1407:
					return new \VKApiException( 1407, 'Too many albums', $error );
				case 1408:
					return new \VKApiException( 1408, 'Item has bad links in description', $error );
				case 1416:
					return new \VKApiException( 1416, 'Variant not found', $error );
				case 1417:
					return new \VKApiException( 1417, 'Property not found', $error );
				case 1425:
					return new \VKApiException( 1425, 'Grouping must have two or more items', $error );
				case 1426:
					return new \VKApiException( 1426, 'Item must have distinct properties', $error );
				case 1600:
					return new \VKApiException( 1600, 'Story has already expired', $error );
				case 1602:
					return new \VKApiException( 1602, 'Incorrect reply privacy', $error );
				case 1900:
					return new \VKApiException( 1900, 'Card not found', $error );
				case 1901:
					return new \VKApiException( 1901, 'Too many cards', $error );
				case 1902:
					return new \VKApiException( 1902, 'Card is connected to post', $error );
				case 2000:
					return new \VKApiException( 2000, 'Servers number limit is reached', $error );
				default:
					return new \VKApiException( $error->getErrorCode(), $error->getErrorMsg(), $error );
			}
		}
	}
}
