==== Query
  curl -i -X GET \
   "https://graph.facebook.com/v15.0/105331307955182/subscribed_apps?access_token=<access token sanitized>"
==== Access Token Info
  {
    "perms": [
      "read_insights",
      "pages_show_list",
      "ads_management",
      "ads_read",
      "business_management",
      "pages_manage_metadata",
      "public_profile"
    ],
    "page_id": 105331307955182,
    "user_id": 1169904517241846,
    "app_id": 489072196489007
  }
==== Parameters
- Query Parameters


  {}
- POST Parameters


  {}
==== Response
  {
    "data": []
  }
==== Debug Information from Graph API Explorer
- https://developers.facebook.com/tools/explorer/?method=GET&path=105331307955182%2Fsubscribed_apps&version=v15.0