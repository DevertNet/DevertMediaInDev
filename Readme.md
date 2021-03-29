# DevertMediaInDev

Download media images from staging or live-system if they not exists.

Often you have the problem that you set up a local test system without downloading the large media folder. So you have a bunch of 404 errors.
This plugin downloads the required images on the fly. But always only those that are really called in the frontend.

For this the plugin hangs itself in the `Legacy_Struct_Converter_Convert_Media` event and checks if the respective file is available locally. If the file is missing, it will be downloaded.

Example how the plugin works: https://example.ddev.site/media/test.jpg

1. Check if the file /media/test.jpg is missing locally
2. Download it from https://example.com/media/test.jpg to /media/test.jpg
3. Done ;)
