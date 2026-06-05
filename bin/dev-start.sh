#!/bin/bash

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${YELLOW}Pull Git...${NC}"

if git pull --rebase; then
    echo -e "${GREEN}Git pull OK${NC}"
else
    echo -e "${RED}Git pull ECHOUÉ (changement ou conflit détecté)${NC}"
    echo -e "${RED}Tu dois stacher ou commiter manuellement${NC}"
fi

echo -e "${YELLOW}Symfony cache & assets...${NC}"

docker exec cn2e-php bash -c "
php bin/console asset-map:c &&
php bin/console c:c
"

echo -e "${GREEN}OK - environnement prêt${NC}"