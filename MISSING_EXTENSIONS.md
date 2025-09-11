# Missing PHP Extensions

Due to network restrictions in the build environment (DNS blocks for `pecl.php.net`), the following PHP extensions from the original Dockerfile could not be installed automatically:

## Extensions to Enable

The following extensions were present in the original Dockerfile and need to be re-enabled once network access is configured:

### PECL Extensions (require network access)
- **redis** - For Redis caching support
- **imagick** - For advanced image processing 
- **wikidiff2** - For MediaWiki diff functionality

### Core Extensions (already included in MediaWiki base image)
- **apcu** - ✅ Already available in base image
- **calendar** - ✅ Already available in base image  
- **intl** - ✅ Already available in base image
- **mysqli** - ✅ Already available in base image
- **luasandbox** - ✅ Already available in base image

### Basic Extensions (currently installed)
- **exif** - ✅ Currently installed
- **zip** - ✅ Currently installed

## How to Enable Missing Extensions

Once network access to `pecl.php.net` is available, uncomment the following line in the Dockerfile:

```dockerfile
# Line 55 in builder stage:
install-php-extensions redis imagick wikidiff2

# Line 144 in final stage:  
install-php-extensions redis imagick wikidiff2
```

Alternatively, add these domains to the allowlist in the repository's [Copilot coding agent settings](https://github.com/TheWanderingCitizen/scwiki-cn-docker-images/settings/copilot/coding_agent):
- `pecl.php.net`
- `dl-ssl.google.com` (for imagick dependencies)

## Current Status
- ✅ Base image migration complete
- ✅ Core PHP extensions preserved from base image
- ✅ Basic extensions (exif, zip) installed
- ⏳ PECL extensions (redis, imagick, wikidiff2) ready to enable