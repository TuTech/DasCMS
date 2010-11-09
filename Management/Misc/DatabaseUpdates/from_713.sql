INSERT
	INTO relContentsClassesChainedContents(ownerContentREL, chainingClassREL, chainedContentREL)
	SELECT
			relContentsPreviewImages.contentREL AS 'ownerContentREL',
			Classes.classID AS 'chainingClassREL',
			relContentsPreviewImages.previewREL AS 'chainedContentREL'
		FROM relContentsPreviewImages
			JOIN Classes
			WHERE Classes.class = 'WImage';