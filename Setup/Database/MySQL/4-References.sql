-- Foreign keys for Aliases
ALTER TABLE 
AccessLog
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;

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
        REFERENCES ChangedByUsers(changedByUserID)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT;
        
        
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

-- link event dates to their contents        
ALTER TABLE 
EventDates
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;
        
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
        
-- content locations
ALTER TABLE 
relContentsLocations
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (locationREL)
        REFERENCES Locations(locationID)
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
       
-- Foreign keys for PersonAttributes
ALTER TABLE 
PersonAttributes
    ADD FOREIGN KEY (personAttributeTypeREL)
        REFERENCES PersonAttributeTypes(personAttributeTypeID)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT;
        
-- Foreign keys for PersonAttributeContexts
ALTER TABLE 
PersonAttributeContexts
    ADD FOREIGN KEY (personContextREL)
        REFERENCES PersonContexts(personContextID)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT,
    ADD FOREIGN KEY (personAttributeREL)
        REFERENCES PersonAttributes(personAttributeID)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT;
        
-- Foreign keys for PersonPrimaryAttributes
ALTER TABLE 
PersonPrimaryAttributes
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;
        
-- Foreign keys for PersonData
ALTER TABLE 
relPersonsRoles
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (personRoleREL)
        REFERENCES PersonRoles(personRoleID)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT;

-- Foreign keys for PersonData
ALTER TABLE 
relPersonsPermissions
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (personPermissionREL)
        REFERENCES PersonPermissions(personPermissionID)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT;
        
-- Foreign keys for PersonData
ALTER TABLE 
relPersonsPermissionTags
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (tagREL)
        REFERENCES Tags(tagID)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT;

-- Foreign keys for PersonData
ALTER TABLE 
PersonData
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (personAttributeContextREL)
        REFERENCES PersonAttributeContexts(personAttributeContextID)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT;

-- Foreign keys for PersonLogins
ALTER TABLE 
PersonLogins
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
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
        
-- Foreign keys for relContentsClassesChainedContents
ALTER TABLE 
relContentsClassesChainedContents
    ADD CONSTRAINT owner FOREIGN KEY (ownerContentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD CONSTRAINT chainingClass FOREIGN KEY (chainingClassREL)
        REFERENCES Classes(classID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD CONSTRAINT chained FOREIGN KEY (chainedContentREL)
        REFERENCES Contents(contentID)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION;

-- Foreign keys for relClassesChainedContents
ALTER TABLE 
relClassesChainedContents
    ADD FOREIGN KEY (chainingClassREL)
        REFERENCES Classes(classID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (chainedContentREL)
        REFERENCES Contents(contentID)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION;
        
-- foreign keys for relContentsFormatters
ALTER TABLE 
relContentsFormatters
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (formatterREL)
        REFERENCES Formatters(formatterID)
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

-- Foreign keys for Aliases
ALTER TABLE 
SearchConfig
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;
        
ALTER TABLE 
SearchIndexOutdated
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;
        
ALTER TABLE 
SearchIndex
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (searchAttributeWeightREL)
        REFERENCES SearchAttributeWeights(searchAttributeWeightID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (searchFeatureREL)
        REFERENCES SearchFeatures(searchFeatureID)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT;
        
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
        
-- foreign keys for atom imports
ALTER TABLE 
SporeViews
    ADD FOREIGN KEY (defaultContentREL)
        REFERENCES Contents(contentID)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (errorContentREL)
        REFERENCES Contents(contentID)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION;

-- content target view
ALTER TABLE 
relContentsTargetViews
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (viewREL)
        REFERENCES SporeViews(viewID)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION;
        
        

ALTER TABLE 
ReaggregateContents
    ADD FOREIGN KEY (contentAggregatorREL)
        REFERENCES ContentAggregators(contentAggregatorID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;

ALTER TABLE 
relAggregatorsContents
    ADD FOREIGN KEY (contentAggregatorREL)
        REFERENCES ContentAggregators(contentAggregatorID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;

ALTER TABLE 
relContentsAggregator
    ADD FOREIGN KEY (contentAggregatorREL)
        REFERENCES ContentAggregators(contentAggregatorID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;

        
        