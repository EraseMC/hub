<?php

declare(strict_types=1);

namespace erase\hub\core\l10n;

enum KnownTranslationKeys : string
{
	case JOIN_TITLE = 'join_title';
	case JOIN_SUBTITLE = 'join_subtitle';
	case JOIN_WELCOME_HEADER = 'join_welcome_header';
	case JOIN_WELCOME_SERVER_LINE = 'join_welcome_server_line';
	case SCOREBOARD_SERVER_LINE = 'scoreboard_server_line';
	case SCOREBOARD_DOMAIN = 'scoreboard_domain';
	case NPC_NAMETAG = 'npc_nametag';
	case FORM_SELECTOR_TITLE = 'form_selector_title';
	case FORM_SELECTOR_CONTENT = 'form_selector_content';
	case FORM_SELECTOR_BUTTON = 'form_selector_button';
	case FORM_SERVER_TITLE = 'form_server_title';
	case FORM_SERVER_CONTENT = 'form_server_content';
	case FORM_BUTTON_CONNECT = 'form_button_connect';
	case FORM_BUTTON_CLOSE = 'form_button_close';
	case STATUS_OFFLINE = 'status_offline';
	case CONNECTING = 'connecting';
	case CONNECT_OFFLINE = 'connect_offline';
	case CONNECT_TRANSFER_MESSAGE = 'connect_transfer_message';
	case ITEM_SELECTOR_NAME = 'item_selector_name';
	case COMMAND_HUB_SUCCESS = 'command_hub_success';
	case COMMAND_SETSPAWN_SUCCESS = 'command_setspawn_success';
	case COMMAND_ONLY_PLAYER = 'command_only_player';
	case COMMAND_NO_PERMISSION = 'command_no_permission';
}
