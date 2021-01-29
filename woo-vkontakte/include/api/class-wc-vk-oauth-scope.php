<?php


class VKOAuthUserScope {
	//type
	const CODE = 'code';
	const TOKEN = 'token';

	//display
	const PAGE = 'page';
	const POPUP = 'popup';
	const MOBILE = 'mobile';

	//user scope
	const U_NOTIFY = 1;
	const U_FRIENDS = 2;
	const U_PHOTOS = 4;
	const U_AUDIO = 8;
	const U_VIDEO = 16;
	const U_PAGES = 32;
	const U_LINK = 256;
	const U_STATUS = 1024;
	const U_NOTES = 2048;
	const U_MESSAGES = 4096;
	const U_WALL = 8192;
	const U_ADS = 32768;
	const U_OFFLINE = 65536;
	const U_DOCS = 131072;
	const U_GROUPS = 262144;
	const U_NOTIFICATIONS = 524288;
	const U_STATS = 1048576;
	const U_EMAIL = 4194304;
	const U_MARKET = 134217728;

	//group scope
	const G_PHOTOS = 4;
	const G_APP_WIDGET = 64;
	const G_MESSAGES = 4096;
	const G_DOCS = 131072;
	const G_MANAGE = 262144;
}