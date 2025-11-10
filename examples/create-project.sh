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
    "title": "Lara & Kens'\''s Wedding Album",
    "customer": {
      "firstname": "Lara",
      "lastname": "Croft",
      "email": "lara.croft@example.com",
      "address": {
        "line1": "Musterstraße 123",
        "line2": "Apartment 4B",
        "city": "Frankfurt",
        "postal_code": "10115",
        "country": "DE"
      }
    },
    "upload": {
      "numberOfImages": 800,
      "coverUrl": "https://picsum.photos/1000/800.jpg",
      "photoUrls": [
        "https://picsum.photos/seed/wedding1/1000/800.jpg",
        "https://picsum.photos/seed/wedding2/1000/800.jpg",
        "https://picsum.photos/seed/wedding3/1000/800.jpg",
        "https://picsum.photos/seed/wedding4/1000/800.jpg"
      ]
    },
    "locale": "en_US"
  }')

# Extract status code (last line) and body (everything before)
http_code=$(echo "$response" | tail -n1)
body=$(echo "$response" | sed '$d')

if [ "$http_code" = "201" ]; then
  echo "✅ Project created successfully!"
  echo "$body" | jq -r '"Project ID: \(.result.projectId)\nRedirect your customer to: \(.result.redirectUrl)"'
else
  echo "❌ Error creating project (HTTP $http_code):"
  echo "$body" | jq .
fi
