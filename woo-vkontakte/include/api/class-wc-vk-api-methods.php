<?php


if ( ! class_exists( 'VKMethods' ) ) {

	/**
	 * Class VKMethods
	 */
	class VKMethods {

		/**
		 * @var \VKApiRequest
		 */
		private $request;

		/**
		 * Methods constructor.
		 *
		 * @param \VKApiRequest $request
		 */
		public function __construct( \VKApiRequest $request ) {
			$this->request = $request;
		}

		/* Database
		--------------------------- */

		/**
		 * Returns a list of cities.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws \VKClientException
		 * @throws \VKApiException
		 * @var integer country_id: Country ID.
		 * - @var integer region_id: Region ID.
		 * - @var string q: Search query.
		 * - @var boolean need_all: '1' — to return all cities in the country, '0' — to return major cities in the country (default),
		 * - @var integer offset: Offset needed to return a specific subset of cities.
		 * - @var integer count: Number of cities to return.
		 */
		public function database_getCities( array $params = [] ) {
			return $this->request->post( 'database.getCities', $params );
		}

		/**
		 * Returns information about cities by their IDs.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws \VKApiException
		 * @throws \VKClientException
		 * @var array[integer] city_ids: City IDs.
		 */
		public function database_getCitiesById( array $params = [] ) {
			return $this->request->post( 'database.getCitiesById', $params );
		}

		/**
		 * Returns a list of countries.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws \VKApiException
		 * @throws \VKClientException
		 * @var integer count: Number of countries to return.
		 * @var boolean need_all: '1' — to return a full list of all countries, '0' — to return a list of countries near the current user's country (default).
		 * - @var string code: Country codes in [vk.com/dev/country_codes|ISO 3166-1 alpha-2] standard.
		 * - @var integer offset: Offset needed to return a specific subset of countries.
		 */
		public function database_getCountries(array $params = []) {
			return $this->request->post('database.getCountries', $params);
		}

		/**
		 * Returns information about countries by their IDs.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws \VKApiException
		 * @throws \VKClientException
		 * @var array[integer] country_ids: Country IDs.
		 */
		public function database_getCountriesById( array $params = [] ) {
			return $this->request->post( 'database.getCountriesById', $params );
		}

		/**
		 * Returns a list of regions.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws \VKApiException
		 * @throws \VKClientException
		 * @var integer count: Number of regions to return.
		 * @var integer country_id: Country ID, received in [vk.com/dev/database.getCountries|database.getCountries] method.
		 * - @var string q: Search query.
		 * - @var integer offset: Offset needed to return specific subset of regions.
		 */
		public function database_getRegions(array $params = []) {
			return $this->request->post('database.getRegions', $params);
		}

		/* Groups
		--------------------------- */

		/**
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 * @var string secret_key
		 * @var integer group_id
		 * - @var string url
		 * - @var string title
		 */
		public function groups_addCallbackServer(array $params = []) {
			return $this->request->post('groups.addCallbackServer', $params, 'group');
		}

		/**
		 * @param string $access_token
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 * @var integer server_id
		 * @var integer group_id
		 */

		public function groups_deleteCallbackServer(array $params = []) {
			return $this->request->post('groups.deleteCallbackServer', $params, 'group');
		}

		/**
		 * Returns Callback API confirmation code for the community.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 * @var integer group_id: Community ID.
		 */
		public function groups_getCallbackConfirmationCode( array $params = [] ) {
			return $this->request->post( 'groups.getCallbackConfirmationCode', $params, 'group' );
		}

		/**
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 * @var array[integer] server_ids
		 * @var integer group_id
		 */
		public function groups_getCallbackServers(array $params = []) {
			return $this->request->post('groups.getCallbackServers', $params, 'group');
		}

		/**
		 * Returns [vk.com/dev/callback_api|Callback API] notifications settings.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 * @var integer server_id: Server ID.
		 * @var integer group_id: Community ID.
		 */
		public function groups_getCallbackSettings(array $params = []) {
			return $this->request->post('groups.getCallbackSettings', $params, 'group');
		}

		/**
		 * Returns a list of requests to the community.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 * @var array[GroupsFields] fields: Profile fields to return.
		 * @var integer group_id: Community ID.
		 * - @var integer offset: Offset needed to return a specific subset of results.
		 * - @var integer count: Number of results to return.
		 */
		public function groups_getRequests(array $params = []) {
			return $this->request->post('groups.getRequests', $params);
		}

		/**
		 * Allow to set notifications settings for group.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKClientException
		 * @throws VKApiException
		 * @var integer group_id: Community ID.
		 * - @var integer server_id: Server ID.
		 * - @var string api_version
		 * - @var boolean message_new: A new incoming message has been received ('0' — disabled, '1' — enabled).
		 * - @var boolean message_reply: A new outcoming message has been received ('0' — disabled, '1' — enabled).
		 * - @var boolean message_allow: Allowed messages notifications ('0' — disabled, '1' — enabled).
		 * - @var boolean message_edit
		 * - @var boolean message_deny: Denied messages notifications ('0' — disabled, '1' — enabled).
		 * - @var boolean message_typing_state
		 * - @var boolean photo_new: New photos notifications ('0' — disabled, '1' — enabled).
		 * - @var boolean audio_new: New audios notifications ('0' — disabled, '1' — enabled).
		 * - @var boolean video_new: New videos notifications ('0' — disabled, '1' — enabled).
		 * - @var boolean wall_reply_new: New wall replies notifications ('0' — disabled, '1' — enabled).
		 * - @var boolean wall_reply_edit: Wall replies edited notifications ('0' — disabled, '1' — enabled).
		 * - @var boolean wall_reply_delete: A wall comment has been deleted ('0' — disabled, '1' — enabled).
		 * - @var boolean wall_reply_restore: A wall comment has been restored ('0' — disabled, '1' — enabled).
		 * - @var boolean wall_post_new: New wall posts notifications ('0' — disabled, '1' — enabled).
		 * - @var boolean wall_repost: New wall posts notifications ('0' — disabled, '1' — enabled).
		 * - @var boolean board_post_new: New board posts notifications ('0' — disabled, '1' — enabled).
		 * - @var boolean board_post_edit: Board posts edited notifications ('0' — disabled, '1' — enabled).
		 * - @var boolean board_post_restore: Board posts restored notifications ('0' — disabled, '1' — enabled).
		 * - @var boolean board_post_delete: Board posts deleted notifications ('0' — disabled, '1' — enabled).
		 * - @var boolean photo_comment_new: New comment to photo notifications ('0' — disabled, '1' — enabled).
		 * - @var boolean photo_comment_edit: A photo comment has been edited ('0' — disabled, '1' — enabled).
		 * - @var boolean photo_comment_delete: A photo comment has been deleted ('0' — disabled, '1' — enabled).
		 * - @var boolean photo_comment_restore: A photo comment has been restored ('0' — disabled, '1' — enabled).
		 * - @var boolean video_comment_new: New comment to video notifications ('0' — disabled, '1' — enabled).
		 * - @var boolean video_comment_edit: A video comment has been edited ('0' — disabled, '1' — enabled).
		 * - @var boolean video_comment_delete: A video comment has been deleted ('0' — disabled, '1' — enabled).
		 * - @var boolean video_comment_restore: A video comment has been restored ('0' — disabled, '1' — enabled).
		 * - @var boolean market_comment_new: New comment to market item notifications ('0' — disabled, '1' — enabled).
		 * - @var boolean market_comment_edit: A market comment has been edited ('0' — disabled, '1' — enabled).
		 * - @var boolean market_comment_delete: A market comment has been deleted ('0' — disabled, '1' — enabled).
		 * - @var boolean market_comment_restore: A market comment has been restored ('0' — disabled, '1' — enabled).
		 * - @var boolean poll_vote_new: A vote in a public poll has been added ('0' — disabled, '1' — enabled).
		 * - @var boolean group_join: Joined community notifications ('0' — disabled, '1' — enabled).
		 * - @var boolean group_leave: Left community notifications ('0' — disabled, '1' — enabled).
		 * - @var boolean group_change_settings
		 * - @var boolean group_change_photo
		 * - @var boolean group_officers_edit
		 * - @var boolean user_block: User added to community blacklist
		 * - @var boolean user_unblock: User removed from community blacklist
		 * - @var boolean lead_forms_new: New form in lead forms
		 */
		public function groups_setCallbackSettings( array $params = [] ) {
			return $this->request->post( 'groups.setCallbackSettings', $params, 'group' );
		}

		/* Market
		--------------------------- */

		/**
		 * Ads a new item to the market.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKClientException
		 * @throws VKApiException
		 * @var integer owner_id: ID of an item owner community.
		 * - @var string name: Item name.
		 * - @var string description: Item description.
		 * - @var integer category_id: Item category ID.
		 * - @var number price: Item price.
		 * - @var number old_price
		 * - @var boolean deleted: Item status ('1' — deleted, '0' — not deleted).
		 * - @var integer main_photo_id: Cover photo ID.
		 * - @var array[integer] photo_ids: IDs of additional photos.
		 * - @var string url: Url for button in market item.
		 */
		public function market_add( array $params = [] ) {
			return $this->request->post( 'market.add', $params );
		}

		/**
		 * Creates new collection of items
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 * @var boolean main_album: Set as main ('1' – set, '0' – no).
		 * @var integer owner_id: ID of an item owner community.
		 * - @var string title: Collection title.
		 * - @var integer photo_id: Cover photo ID.
		 */
		public function market_addAlbum(array $params = []) {
			return $this->request->post('market.addAlbum', $params);
		}

		/**
		 * Adds an item to one or multiple collections.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 * @var integer owner_id: ID of an item owner community.
		 * - @var integer item_id: Item ID.
		 * - @var array[integer] album_ids: Collections IDs to add item to.
		 */
		public function market_addToAlbum( array $params = [] ) {
			return $this->request->post( 'market.addToAlbum', $params );
		}

		/**
		 * Deletes an item.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 * @var integer item_id: Item ID.
		 * @var integer owner_id: ID of an item owner community.
		 */
		public function market_delete(array $params = []) {
			return $this->request->post('market.delete', $params);
		}

		/**
		 * Deletes a collection of items.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 * @var integer album_id: Collection ID.
		 * @var integer owner_id: ID of an collection owner community.
		 */
		public function market_deleteAlbum(array $params = []) {
			return $this->request->post('market.deleteAlbum', $params);
		}

		/**
		 * Edits an item.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKClientException
		 * @throws VKApiException
		 * @var integer owner_id: ID of an item owner community.
		 * - @var integer item_id: Item ID.
		 * - @var string name: Item name.
		 * - @var string description: Item description.
		 * - @var integer category_id: Item category ID.
		 * - @var number price: Item price.
		 * - @var boolean deleted: Item status ('1' — deleted, '0' — not deleted).
		 * - @var integer main_photo_id: Cover photo ID.
		 * - @var array[integer] photo_ids: IDs of additional photos.
		 * - @var string url: Url for button in market item.
		 */
		public function market_edit( array $params = [] ) {
			return $this->request->post( 'market.edit', $params );
		}

		/**
		 * Edits a collection of items
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 * @var integer photo_id: Cover photo id
		 * - @var boolean main_album: Set as main ('1' – set, '0' – no).
		 * @var integer owner_id: ID of an collection owner community.
		 * - @var integer album_id: Collection ID.
		 * - @var string title: Collection title.
		 */
		public function market_editAlbum(array $params = []) {
			return $this->request->post('market.editAlbum', $params);
		}

		/**
		 * Returns items list for a community.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 * @var integer offset: Offset needed to return a specific subset of results.
		 * - @var boolean extended: '1' – method will return additional fields: 'likes, can_comment, car_repost, photos'. These parameters are not returned by default.
		 * @var integer owner_id: ID of an item owner community, "Note that community id in the 'owner_id' parameter should be negative number. For example 'owner_id'=-1 matches the [vk.com/apiclub|VK API] community "
		 * - @var integer album_id
		 * - @var integer count: Number of items to return.
		 */
		public function market_get(array $params = []) {
			return $this->request->post('market.get', $params);
		}

		/**
		 * Return order by id
		 *
		 * @param array $params
		 *
		 * @return mixed
		 * @throws VKClientException
		 * @throws VKApiException
		 */
		public function market_getOrderById( array $params = [] ) {
			return $this->request->post( 'market.getOrderById', $params );
		}

		/**
		 * Return order items
		 *
		 * @param array $params
		 *
		 * @return array|mixed|null
		 * @throws VKClientException
		 * @throws VKApiException
		 */
		public function market_getOrderItems( array $params = [] ) {
			return $this->request->post( 'market.getOrderItems', $params );
		}

		/**
		 * Returns items album's data
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 * @var array[integer] album_ids: collections identifiers to obtain data from
		 * @var integer owner_id: identifier of an album owner community, "Note that community id in the 'owner_id' parameter should be negative number. For example 'owner_id'=-1 matches the [vk.com/apiclub|VK API] community "
		 */
		public function market_getAlbumById(array $params = []) {
			return $this->request->post('market.getAlbumById', $params);
		}

		/**
		 * Returns community's collections list.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 * @var integer owner_id: ID of an items owner community.
		 * - @var integer offset: Offset needed to return a specific subset of results.
		 * - @var integer count: Number of items to return.
		 */
		public function market_getAlbums( array $params = [] ) {
			return $this->request->post( 'market.getAlbums', $params );
		}

		/**
		 * Returns information about market items by their ids.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 * @var boolean extended: '1' – to return additional fields: 'likes, can_comment, car_repost, photos'. By default: '0'.
		 * @var array[string] item_ids: Comma-separated ids list: {user id}_{item id}. If an item belongs to a community -{community id} is used. " 'Videos' value example: , '-4363_136089719,13245770_137352259'"
		 */
		public function market_getById(array $params = []) {
			return $this->request->post('market.getById', $params);
		}

		/**
		 * Returns a list of market categories.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 * @var integer offset: Offset needed to return a specific subset of results.
		 * @var integer count: Number of results to return.
		 */
		public function market_getCategories(array $params = []) {
			return $this->request->post('market.getCategories', $params);
		}

		/**
		 * Edit order
		 *
		 * @param array $params
		 *
		 * @return mixed
		 * @throws VKClientException
		 * @throws VKApiException
		 */
		public function market_editOrder( array $params = [] ) {
			return $this->request->post( 'market.editOrder', $params );
		}

		/* Orders
		--------------------------- */

		/**
		 * Returns a list of orders.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 * @var integer offset
		 * - @var integer count: number of returned orders.
		 * - @var boolean test_mode: if this parameter is set to 1, this method returns a list of test mode orders. By default — 0.
		 */
		public function orders_get( array $params = [] ) {
			return $this->request->post( 'orders.get', $params );
		}

		/**
		 * Returns information about orders by their IDs.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 * @var integer order_id: order ID.
		 * - @var array[integer] order_ids: order IDs (when information about several orders is requested).
		 * - @var boolean test_mode: if this parameter is set to 1, this method returns a list of test mode orders. By default — 0.
		 */
		public function orders_getById( array $params = [] ) {
			return $this->request->post( 'orders.getById', $params );
		}

		/* Photos
		--------------------------- */

		/**
		 * Deletes a photo.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 * @var integer photo_id: Photo ID.
		 * @var integer owner_id: ID of the user or community that owns the photo.
		 */
		public function photos_delete(array $params = []) {
			return $this->request->post('photos.delete', $params);
		}

		/**
		 * Returns the server address for market album photo upload.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 * @var integer group_id: Community ID.
		 */
		public function photos_getMarketAlbumUploadServer( array $params = [] ) {
			return $this->request->post( 'photos.getMarketAlbumUploadServer', $params );
		}

		/**
		 * Returns the server address for market photo upload.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 * @var integer crop_y: Y coordinate of the crop left upper corner.
		 * - @var integer crop_width: Width of the cropped photo in px.
		 * @var integer group_id: Community ID.
		 * - @var boolean main_photo: '1' if you want to upload the main item photo.
		 * - @var integer crop_x: X coordinate of the crop left upper corner.
		 */
		public function photos_getMarketUploadServer(array $params = []) {
			return $this->request->post('photos.getMarketUploadServer', $params);
		}

		/**
		 * Saves photos after successful uploading.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKClientException
		 * @throws VKApiException
		 * @var integer album_id: ID of the album to save photos to.
		 * - @var integer group_id: ID of the community to save photos to.
		 * - @var integer server: Parameter returned when photos are [vk.com/dev/upload_files|uploaded to server].
		 * - @var string photos_list: Parameter returned when photos are [vk.com/dev/upload_files|uploaded to server].
		 * - @var string hash: Parameter returned when photos are [vk.com/dev/upload_files|uploaded to server].
		 * - @var number latitude: Geographical latitude, in degrees (from '-90' to '90').
		 * - @var number longitude: Geographical longitude, in degrees (from '-180' to '180').
		 * - @var string caption: Text describing the photo. 2048 digits max.
		 */
		public function photos_save( array $params = [] ) {
			return $this->request->post( 'photos.save', $params );
		}

		/**
		 * Saves market album photos after successful uploading.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 * @var string hash: Parameter returned when photos are [vk.com/dev/upload_files|uploaded to server].
		 * @var integer group_id: Community ID.
		 * - @var string photo: Parameter returned when photos are [vk.com/dev/upload_files|uploaded to server].
		 * - @var integer server: Parameter returned when photos are [vk.com/dev/upload_files|uploaded to server].
		 */
		public function photos_saveMarketAlbumPhoto(array $params = []) {
			return $this->request->post('photos.saveMarketAlbumPhoto', $params);
		}

		/**
		 * Saves market photos after successful uploading.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKClientException
		 * @throws VKApiException
		 * @var integer group_id: Community ID.
		 * - @var string photo: Parameter returned when photos are [vk.com/dev/upload_files|uploaded to server].
		 * - @var integer server: Parameter returned when photos are [vk.com/dev/upload_files|uploaded to server].
		 * - @var string hash: Parameter returned when photos are [vk.com/dev/upload_files|uploaded to server].
		 * - @var string crop_data: Parameter returned when photos are [vk.com/dev/upload_files|uploaded to server].
		 * - @var string crop_hash: Parameter returned when photos are [vk.com/dev/upload_files|uploaded to server].
		 */
		public function photos_saveMarketPhoto( array $params = [] ) {
			return $this->request->post( 'photos.saveMarketPhoto', $params );
		}

		/* Users
		--------------------------- */

		/**
		 * Returns detailed information on users.
		 *
		 * @param array $params
		 * - @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 * @var array[string] user_ids: User IDs or screen names ('screen_name'). By default, current user ID.
		 * - @var array[UsersFields] fields: Profile fields to return. Sample values: 'nickname', 'screen_name', 'sex', 'bdate' (birthdate), 'city', 'country', 'timezone', 'photo', 'photo_medium', 'photo_big', 'has_mobile', 'contacts', 'education', 'online', 'counters', 'relation', 'last_seen', 'activity', 'can_write_private_message', 'can_see_all_posts', 'can_post', 'universities',
		 * - @var string name_case: Case for declension of user name and surname: 'nom' — nominative (default), 'gen' — genitive , 'dat' — dative, 'acc' — accusative , 'ins' — instrumental , 'abl' — prepositional
		 */
		public function users_get( array $params = [] ) {
			return $this->request->post( 'users.get', $params );
		}
	}
}