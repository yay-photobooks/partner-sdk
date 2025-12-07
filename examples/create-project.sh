#!/bin/bash

# Make sure environment variables are set (via .envrc or manually)
# Required: YAY_PARTNER_USERNAME, YAY_PARTNER_PASSWORD, YAY_PARTNER_USER_AGENT, YAY_PARTNER_BASE_URL

# Check required environment variables
if [ -z "${YAY_PARTNER_BASE_URL}" ]; then
  echo "❌ YAY_PARTNER_BASE_URL environment variable is not set"
  echo "Please make sure .envrc is loaded (run: direnv allow)"
  exit 1
fi

if [ -z "${YAY_PARTNER_USERNAME}" ]; then
  echo "❌ YAY_PARTNER_USERNAME environment variable is not set"
  exit 1
fi

if [ -z "${YAY_PARTNER_PASSWORD}" ]; then
  echo "❌ YAY_PARTNER_PASSWORD environment variable is not set"
  exit 1
fi

if [ -z "${YAY_PARTNER_USER_AGENT}" ]; then
  echo "❌ YAY_PARTNER_USER_AGENT environment variable is not set"
  exit 1
fi

echo "📝 Creating photobook project..."

response=$(curl -X POST "${YAY_PARTNER_BASE_URL}/papi/projects" \
  -u "${YAY_PARTNER_USERNAME}:${YAY_PARTNER_PASSWORD}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "User-Agent: ${YAY_PARTNER_USER_AGENT}" \
  -w "\n%{http_code}" \
  -d '{
    "title": "Sarah & Michael Wedding Album",
    "customer": {
      "firstname": "Sarah",
      "lastname": "Example",
      "email": "lara.croft@example.com",
      "address": {
        "line1": "Musterstraße 123",
        "line2": "Apartment 4B",
        "city": "Frankfurt",
        "postal_code": "10115",
        "country": "DE"
      }
    },
    "locale": "en_US",
    "upload": {
      "numberOfImages": 33,
      "coverUrl": "https://s3.yaymemories.com/public/wedding/photo-1519741497674-611481863552.jpg",
      "photoUrls": [
        "https://s3.yaymemories.com/public/wedding/photo-1460978812857-470ed1c77af0.jpg",
        "https://s3.yaymemories.com/public/wedding/photo-1481980235850-66e47651e431.jpg",
        "https://s3.yaymemories.com/public/wedding/photo-1485700281629-290c5a704409.jpg",
        "https://s3.yaymemories.com/public/wedding/photo-1519379169146-d4b170447caa.jpg",
        "https://s3.yaymemories.com/public/wedding/photo-1519741196428-6a2175fa2557.jpg",
        "https://s3.yaymemories.com/public/wedding/photo-1519741497674-611481863552.jpg",
        "https://s3.yaymemories.com/public/wedding/photo-1525772764200-be829a350797.jpg",
        "https://s3.yaymemories.com/public/wedding/photo-1537633552985-df8429e8048b.jpg",
        "https://s3.yaymemories.com/public/wedding/photo-1546032996-6dfacbacbf3f.jpg",
        "https://s3.yaymemories.com/public/wedding/photo-1550784718-990c6de52adf.jpg",
        "https://s3.yaymemories.com/public/wedding/photo-1551468307-8c1e3c78013c.jpg",
        "https://s3.yaymemories.com/public/wedding/photo-1562616382-b884d7188d8a.jpg",
        "https://s3.yaymemories.com/public/wedding/photo-1583939003579-730e3918a45a.jpg",
        "https://s3.yaymemories.com/public/wedding/photo-1591700331354-f7eea65d1ce8.jpg",
        "https://s3.yaymemories.com/public/wedding/photo-1595407753234-0882f1e77954.jpg",
        "https://s3.yaymemories.com/public/wedding/photo-1596457221755-b96bc3a6df18.jpg",
        "https://s3.yaymemories.com/public/wedding/photo-1603214924133-5c2c78471b73.jpg",
        "https://s3.yaymemories.com/public/wedding/photo-1606216794074-735e91aa2c92.jpg",
        "https://s3.yaymemories.com/public/wedding/photo-1606216794079-73f85bbd57d5.jpg",
        "https://s3.yaymemories.com/public/wedding/photo-1606216836537-eea72a939072.jpg",
        "https://s3.yaymemories.com/public/wedding/photo-1607190074257-dd4b7af0309f.jpg",
        "https://s3.yaymemories.com/public/wedding/photo-1607357910286-1ff94ac13c24.jpg",
        "https://s3.yaymemories.com/public/wedding/photo-1607861884586-c7cfaed16290.jpg",
        "https://s3.yaymemories.com/public/wedding/photo-1649183424680-464747a8e43d.jpg",
        "https://s3.yaymemories.com/public/wedding/premium_photo-1663076211121-36754a46de8d.jpg",
        "https://s3.yaymemories.com/public/wedding/premium_photo-1664530452596-e1c17e342876.jpg",
        "https://s3.yaymemories.com/public/wedding/premium_photo-1673897888993-a1db844c2ca1.jpg",
        "https://s3.yaymemories.com/public/wedding/premium_photo-1675003663256-bfdc8b1acb2d.jpg",
        "https://s3.yaymemories.com/public/wedding/premium_photo-1675719847698-6c8a924b2a7a.jpg",
        "https://s3.yaymemories.com/public/wedding/premium_photo-1675851210020-045950ac0215.jpg",
        "https://s3.yaymemories.com/public/wedding/premium_photo-1675851210850-de5525809dd9.jpg",
        "https://s3.yaymemories.com/public/wedding/premium_photo-1711132425055-1c289c69b950.jpg",
        "https://s3.yaymemories.com/public/wedding/sandy-millar-8vaQKYnawHw-unsplash.jpg"
      ]
    }
  }')

# Extract status code (last line) and body (everything before)
http_code=$(echo "$response" | tail -n1)

if [ "$http_code" = "201" ]; then
  echo "✅ Project created successfully!"
else
  echo "❌ Error creating project (HTTP $http_code):"
fi
echo "$response"
