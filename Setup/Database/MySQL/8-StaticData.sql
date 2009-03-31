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


