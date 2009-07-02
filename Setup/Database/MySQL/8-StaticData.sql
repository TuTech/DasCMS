INSERT IGNORE INTO `MailImportFlags` (`mailImportFlagID`, `label`, `flag`) VALUES
(1, 'anonymous', '/anonymous'),
(2, 'secure', '/secure'),
(3, 'no_rsh', '/norsh'),
(4, 'ssl', '/ssl'),
(5, 'validate_certificate', '/validate-cert'),
(6, 'dont_validate_certificate', '/novalidate-cert'),
(7, 'tls', '/tls'),
(8, 'no_tls', '/notls'),
(9, 'read_only', '/readonly');

INSERT IGNORE INTO `Mimetypes` (`mimetypeID`, `mimetype`) VALUES
(1, 'cms/internal');

INSERT IGNORE INTO `PersonAttributeTypes` (`personAttributeTypeID`, `personAttributeType`) VALUES
(1, 'text'),
(2, 'email'),
(3, 'phone'),
(4, 'textbox');

INSERT IGNORE INTO `PersonAttributes` (`personAttributeID`, `personAttribute`, `personAttributeTypeREL`) VALUES
(1, 'person_data', 1),
(2, 'phone', 3),
(3, 'email', 2),
(4, 'instant_messenger', 1),
(5, 'address', 4),
(6, 'additional_information',4);

INSERT INTO `PersonRoles` (`personRoleID`, `personRole`) VALUES
(1, 'administrator'),
(2, 'editor'),
(3, 'user'),
(4, 'web_account'),
(5, 'unprivileged');

INSERT INTO `SearchAttributeWeights` (`searchAttributeWeightID`, `attribute`, `weight`) VALUES
(1, 'Title', 1),
(2, 'Description', 0.7),
(3, 'Content', 0.5),
(4, 'Tags', 1),
(5, 'SubTitle', 0.8);