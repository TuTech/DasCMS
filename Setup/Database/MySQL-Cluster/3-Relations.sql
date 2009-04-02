 CREATE TABLE IF NOT EXISTS relContentsPreviewImages( contentREL INTEGER UNIQUE NOT NULL, previewREL INTEGER NOT NULL, INDEX (previewREL) )  CHARACTER SET utf8 COLLATE utf8_unicode_ci ;
 CREATE TABLE IF NOT EXISTS relContentsTags( contentREL INTEGER NOT NULL, tagREL INTEGER NOT NULL, INDEX (contentREL), INDEX (tagREL) )  CHARACTER SET utf8 COLLATE utf8_unicode_ci ;
 CREATE TABLE IF NOT EXISTS relFeedsContents( feedREL INTEGER NOT NULL, contentREL INTEGER NOT NULL, INDEX (feedREL), INDEX (contentREL) )  CHARACTER SET utf8 COLLATE utf8_unicode_ci ;
 CREATE TABLE IF NOT EXISTS relFeedsTags( feedREL INTEGER NOT NULL, tagREL INTEGER NOT NULL, INDEX (feedREL), INDEX (tagREL) )  CHARACTER SET utf8 COLLATE utf8_unicode_ci ;
 CREATE TABLE IF NOT EXISTS relMailImportAccountsMailImportFlags( mailImportAccountREL INTEGER NOT NULL, mailImportFlagREL INTEGER NOT NULL )  CHARACTER SET utf8 COLLATE utf8_unicode_ci ;
 CREATE TABLE IF NOT EXISTS relPermissionTagsGroups( permissionTagREL INTEGER NOT NULL, groupREL INTEGER NOT NULL, INDEX (permissionTagREL), UNIQUE (groupREL, permissionTagREL) )  CHARACTER SET utf8 COLLATE utf8_unicode_ci ;
 CREATE TABLE IF NOT EXISTS relPermissionTagsUsers( permissionTagREL INTEGER NOT NULL, userREL INTEGER NOT NULL, INDEX (permissionTagREL), UNIQUE (userREL, permissionTagREL) )  CHARACTER SET utf8 COLLATE utf8_unicode_ci ;
 CREATE TABLE IF NOT EXISTS relUsersGroups( userREL INTEGER NOT NULL, groupREL INTEGER NOT NULL, INDEX (userREL), UNIQUE (groupREL, userREL) )  CHARACTER SET utf8 COLLATE utf8_unicode_ci ;
 