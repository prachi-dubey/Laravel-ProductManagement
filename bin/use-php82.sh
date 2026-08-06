#!/usr/bin/env bash
# Usage (from shop-api): source bin/use-php82.sh
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(cd "$DIR/.." && pwd)"
export PHPRC="$ROOT/.php"
export PATH="$DIR:$PATH"
echo "Active PHP: $(command php -v | /usr/bin/head -n 1)"
echo "PHPRC=$PHPRC"
echo "Global default without this PATH remains XAMPP PHP 7.4."
