#!/bin/bash

# populate-wp.sh - Populate WordPress Studio with 20 test posts and unique featured images
# Usage: bash scripts/populate-wp.sh

set -e

# Configuration
WP_PATH="$HOME/Studio/my-wordpress-website"
CATEGORY_NAME="Gallery Test"
POST_COUNT=20
TEMP_DIR="/tmp/wp-populate-images-$$"

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== WordPress Test Post Population ===${NC}"
echo ""

# Check if WP-CLI is available
if ! command -v wp &> /dev/null; then
    echo -e "${RED}Error: WP-CLI is not installed or not in PATH${NC}"
    echo "Please install WP-CLI: https://wp-cli.org/"
    exit 1
fi

# Check if WordPress path exists
if [ ! -d "$WP_PATH" ]; then
    echo -e "${RED}Error: WordPress path not found: $WP_PATH${NC}"
    exit 1
fi

# Check if it's a valid WordPress installation
if ! wp --path="$WP_PATH" core is-installed 2>/dev/null; then
    echo -e "${RED}Error: Not a valid WordPress installation at: $WP_PATH${NC}"
    exit 1
fi

echo -e "${GREEN}✓ WP-CLI found${NC}"
echo -e "${GREEN}✓ WordPress installation verified${NC}"
echo ""

# Create temp directory for images
mkdir -p "$TEMP_DIR"
echo -e "${BLUE}Created temporary directory: $TEMP_DIR${NC}"

# Create category if it doesn't exist
echo -e "${BLUE}Setting up category...${NC}"
CATEGORY_ID=$(wp --path="$WP_PATH" term list category --name="$CATEGORY_NAME" --field=term_id 2>&1 | grep -v "^Deprecated:" | grep -v "^PHP Deprecated:" | tail -n1 || echo "")

if [ -z "$CATEGORY_ID" ]; then
    CATEGORY_ID=$(wp --path="$WP_PATH" term create category "$CATEGORY_NAME" --porcelain 2>&1 | grep -v "^Deprecated:" | grep -v "^PHP Deprecated:" | tail -n1)
    echo -e "${GREEN}✓ Created category: $CATEGORY_NAME (ID: $CATEGORY_ID)${NC}"
else
    echo -e "${GREEN}✓ Using existing category: $CATEGORY_NAME (ID: $CATEGORY_ID)${NC}"
fi

echo ""
echo -e "${BLUE}Creating $POST_COUNT posts with unique featured images...${NC}"
echo ""

# Create posts with unique featured images
for i in $(seq 1 $POST_COUNT); do
    echo -e "${BLUE}[$i/$POST_COUNT] Processing...${NC}"

    # Generate unique image URL from picsum.photos (800x600, different seed each time)
    IMAGE_URL="https://picsum.photos/seed/post-$i/800/600"
    IMAGE_PATH="$TEMP_DIR/image-$i.jpg"

    # Download image
    echo "  - Downloading image from picsum.photos..."
    if curl -s -L -o "$IMAGE_PATH" "$IMAGE_URL"; then
        echo -e "  ${GREEN}✓ Downloaded image${NC}"
    else
        echo -e "  ${RED}✗ Failed to download image, skipping post${NC}"
        continue
    fi

    # Create post
    POST_TITLE="Test Post $i"
    POST_CONTENT="<p>This is test post number $i. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p><p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>"

    POST_ID=$(wp --path="$WP_PATH" post create \
        --post_title="$POST_TITLE" \
        --post_content="$POST_CONTENT" \
        --post_status=publish \
        --post_category="$CATEGORY_ID" \
        --porcelain 2>&1 | grep -v "^Deprecated:" | grep -v "^PHP Deprecated:" | tail -n1)

    echo -e "  ${GREEN}✓ Created post: $POST_TITLE (ID: $POST_ID)${NC}"

    # Import image to media library
    ATTACHMENT_ID=$(wp --path="$WP_PATH" media import "$IMAGE_PATH" \
        --post_id="$POST_ID" \
        --title="Featured Image $i" \
        --porcelain 2>&1 | grep -v "^Deprecated:" | grep -v "^PHP Deprecated:" | grep -v "^Warning:" | tail -n1)

    echo -e "  ${GREEN}✓ Uploaded image (ID: $ATTACHMENT_ID)${NC}"

    # Set as featured image
    wp --path="$WP_PATH" post meta set "$POST_ID" _thumbnail_id "$ATTACHMENT_ID" > /dev/null
    echo -e "  ${GREEN}✓ Set as featured image${NC}"

    echo ""
done

# Cleanup temp directory
echo -e "${BLUE}Cleaning up temporary files...${NC}"
rm -rf "$TEMP_DIR"
echo -e "${GREEN}✓ Cleanup complete${NC}"

echo ""
echo -e "${GREEN}=== Success! ===${NC}"
echo -e "Created ${GREEN}$POST_COUNT posts${NC} in category '${GREEN}$CATEGORY_NAME${NC}'"
echo -e "Each post has a ${GREEN}unique featured image${NC}"
echo ""
echo -e "${BLUE}Test URLs:${NC}"
echo "  Category archive: http://localhost:8881/category/gallery-test/"
echo "  Gallery view: http://localhost:8881/category/gallery-test/gallery/"
echo ""
