# dashcam-geodb
Creates geospatial PostGIS database from BlackVue dashcam videos. Work in progress.

## How it works
This application "translates" geographical metadata embedded in dashcam videos
to named places and venues, and inserts these into the metadata section of the
file. Thus, the video files can be made directly searchable by geographical
names.

Works for Norwegian venues as of now.

Geographical information are retrieved as GML files, parsed and inserted into
a PostGIS server. Each video is then searched for GPS information, and any
proximity to a named area will be registered and injected into the the video
file's EXIF block.
