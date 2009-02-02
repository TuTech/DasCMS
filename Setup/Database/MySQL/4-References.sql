-- Foreign keys for Aliases
ALTER TABLE 
Aliases
    ADD CONSTRAINT assigned_content FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;


-- foreign keys for atom imports
ALTER TABLE 
AtomImports
    ADD FOREIGN KEY (atomSourceREL)
        REFERENCES AtomSources(atomSourceID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;


-- Foreign keys for Changes
ALTER TABLE 
Changes
    ADD CONSTRAINT changed_content FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD CONSTRAINT changed_by FOREIGN KEY (userREL)
        REFERENCES Users(userID)
        ON DELETE SET NULL
        ON UPDATE NO ACTION;
        
        
-- Foreign keys for Contents
ALTER TABLE 
Contents
    ADD CONSTRAINT content_class FOREIGN KEY (type)
        REFERENCES Classes(classID)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT,
    ADD CONSTRAINT content_mimetype FOREIGN KEY (mimetypeREL)
        REFERENCES Mimetypes(mimetypeID)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT,
    ADD CONSTRAINT primary_alias FOREIGN KEY (primaryAlias) 
        REFERENCES Aliases(aliasID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD CONSTRAINT content_guid FOREIGN KEY (GUID) 
        REFERENCES Aliases(aliasID)
        ON DELETE CASCADE
        ON UPDATE RESTRICT;
        
        
-- foreign keys for cfile attributes
ALTER TABLE 
FileAttributes
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (folderREL)
        REFERENCES Folders(folderID)
        ON DELETE SET NULL
        ON UPDATE NO ACTION;


-- Foreign keys for relFeedsTags
ALTER TABLE 
Feeds
    ADD CONSTRAINT changed_feed FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;
        
        
-- foreign keys for cfile folders
ALTER TABLE 
Folders
    ADD FOREIGN KEY (parentFolderREL)
        REFERENCES Folders(folderID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;


-- Foreign keys for PermissionTags
ALTER TABLE 
Jobs
    ADD FOREIGN KEY (classREL)
        REFERENCES Classes(classID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;

        
-- Foreign keys for PermissionTags
ALTER TABLE 
JobSchedules
    ADD FOREIGN KEY (jobREL)
        REFERENCES Jobs(jobID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;
        
        
-- foreign keys for IMAP Account flags
ALTER TABLE 
MailImportMails
    ADD FOREIGN KEY (mailImportAccountREL)
        REFERENCES MailImportAccounts(mailImportAccountID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE SET NULL
        ON UPDATE SET NULL;

        
-- Foreign keys for PermissionTags
ALTER TABLE 
PermissionTags
    ADD FOREIGN KEY (permissionTagREL)
        REFERENCES Tags(tagID)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION;
        
        
-- Foreign keys for relContentsPreviewImages
ALTER TABLE 
relContentsPreviewImages
    ADD CONSTRAINT content_previewed FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD CONSTRAINT content_preview_provider FOREIGN KEY (previewREL)
        REFERENCES Contents(contentID)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION;
        
 -- Foreign keys for relContentsTags
ALTER TABLE 
relContentsTags
    ADD CONSTRAINT tagged_content FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD CONSTRAINT tagged_with FOREIGN KEY (tagREL)
        REFERENCES Tags(tagID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;
        
        
-- Foreign keys for relFeedsContents
ALTER TABLE 
relFeedsContents
    ADD FOREIGN KEY (feedREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;
        
        
-- Foreign keys for relFeedsTags
ALTER TABLE 
relFeedsTags
    ADD FOREIGN KEY (feedREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (tagREL)
        REFERENCES Tags(tagID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;
        
        
-- foreign keys for IMAP Account flags
ALTER TABLE 
relMailImportAccountsMailImportFlags
    ADD FOREIGN KEY (mailImportAccountREL)
        REFERENCES MailImportAccounts(mailImportAccountID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (mailImportFlagREL)
        REFERENCES MailImportFlags(mailImportFlagID)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION;
        
        
-- Foreign keys for relPermissionTagsGroups
ALTER TABLE 
relPermissionTagsGroups
    ADD FOREIGN KEY (permissionTagREL)
        REFERENCES PermissionTags(permissionTagREL)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (groupREL)
        REFERENCES Groups(groupID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;
        
        
-- Foreign keys for relPermissionTagsUsers
ALTER TABLE 
relPermissionTagsUsers
    ADD FOREIGN KEY (permissionTagREL)
        REFERENCES PermissionTags(permissionTagREL)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (userREL)
        REFERENCES Users(userID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;
        
        
-- Foreign keys for relUsersGroups
ALTER TABLE 
relUsersGroups
    ADD CONSTRAINT group_member FOREIGN KEY (userREL)
        REFERENCES Users(userID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD CONSTRAINT group_relation FOREIGN KEY (groupREL)
        REFERENCES Groups(groupID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;
        
        
-- Foreign keys for Users
ALTER TABLE 
Users
    ADD CONSTRAINT primary_group FOREIGN KEY (primaryGroup)
        REFERENCES Groups(groupID)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION;
