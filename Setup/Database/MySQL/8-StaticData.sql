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
