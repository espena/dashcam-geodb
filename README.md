# dashcam-geodb
Creates geographical location tags for Plex from BlackVue dashcam videos.
Work in progress.

## How it works
This application tranforms geographical coordinates embedded in dashcam videos
from BlackVue cameras to named venues, emitting a SQL file that can be run
against Plex Media Server. The geographical tags are stored as categories
(genres) in the Plex database, as it will make the data searchable from the GUI.

Works for Norwegian venues as of now.

Geographical information are retrieved as GML files, parsed and inserted into
a PostGIS server. Each video is then searched for GPS information, and any
proximity to a named area will be registered.

## Initial configuration
1. Edit `docker/docker-compose.yml` file, mounting the video directory and
   output directory for the plex import file as volumes.
2. Edit the configuration file `configure/dashcam-geodb.ini`, setting the
   correct path to the video folder within the `dcm-php` docker container.
3. In `configure/dashcam-geodb.ini`, also set the correct and complete path to
   the `PLEX_IMPORT_FILE`. This should be in one of the mounted docker volumes.
4. Start the continers with docker-compose up -d from the `docker/` directory.
5. Enter the `dcm-php` container, cd to `/srv/main` and run `./configure.sh`,
   this will populate the PostGIS database. This process will take several
   minutes to complete.

## Run
Enter the `dcm-php` container and cd to `/srv/main`. From there, run
`./parse.sh` to start parsing the video files.
