all:

rel:	release
release:
ifndef v
	# Must specify version as 'v' param
	#
	#   make rel v=1.1.1
	#
else
	#
	# Tagging it with release tag
	#
	svn copy . https://mediawiki-widgets.googlecode.com/svn/tags/REL_${subst .,_,${v}}/
	#
	# Creating release tarball and zip
	#
	svn export . Widgets
	svn export smarty Widgets/smarty
	# Not including Makefile into the package since it's not doing anything but release packaging
	rm Widgets/Makefile
	tar -c Widgets -zf Widgets_${v}.tgz
	zip -r Widgets_${v}.zip Widgets
	rm -rf Widgets

	#
	# Copying tarball and zip to destination
	#
	mv Widgets_${v}.tgz ${dest}
	mv Widgets_${v}.zip ${dest}
#	googlecode_upload.py -s "MediaWiki Widgets Extension v${v} (tarball)" -l "Widgets MediaWiki Extension Featured" -p mediawiki-widgets Widgets_${v}.tgz
#	googlecode_upload.py -s "MediaWiki Widgets Extension v${v} (zip)" -l "Widgets MediaWiki Extension Featured" -p mediawiki-widgets Widgets_${v}.zip
endif
