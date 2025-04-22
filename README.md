# SchoolTool
An intranet for schools, connecting students and staff

# Building the container (FIRST TIME ONLY)
docker compose up --build

# Starting the project
docker compose up

# Database creation and hydration
<!-- Open a terminal and run the following commands, in order to use the sql files in the folder named "database" -->
MYSQL_PWD=root mysql -h 127.0.0.1 -P 3307 -u root < <path_to_the_database_folder>/createAuth.sql
MYSQL_PWD=root mysql -h 127.0.0.1 -P 3307 -u root < <path_to_the_database_folder>/createApi.sql
MYSQL_PWD=root mysql -h 127.0.0.1 -P 3307 -u root < <path_to_the_database_folder>/hydrateAuth.sql
MYSQL_PWD=root mysql -h 127.0.0.1 -P 3307 -u root < <path_to_the_database_folder>/hydrateAuth.sql

# Config files

<!-- In the /front folder, create a file named config.js at the root and add the following -->

export default {
    ANDROID_CLIENT_ID: "462034163728-rb6le77tvp0ktpoft8bb5f47tt2qf340.apps.googleusercontent.com",
    IOS_CLIENT_ID: "462034163728-o3hjarad42dt0gh5beipmq99q4md2fjv.apps.googleusercontent.com",
    WEB_CLIENT_ID:"462034163728-n05qp98g4t5sjjcovkvtmt4vkflveipn.apps.googleusercontent.com",
    LPTF_GOOGLE_CLIENT_ID: "604347883543-cu73up3fqo5r9gn18tqpkf3tu9ud41s4.apps.googleusercontent.com",
    GOOGLE_CLIENT_SECRET: "ENTER YOUR SECRET HERE",    

    LPTF_API_URL:'http://localhost:8000',
    LPTF_AUTH_API_URL :'http://localhost:8001',
  };  .


<!-- In the /back and /auth folders, rename the application/config/constants.php.example file into constants.php -->

docker compose exec front npx expo start --tunnel