# Seatmaster 🪑📅

Repository for Seatmaster project, created as part of my bachelor thesis at FIT CTU 🎓. Full thesis text available at [CTU DSpace](http://hdl.handle.net/10467/113722).

## Getting Started 🧱⏩🏠

These instructions will get your copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites 🛠️

Before you begin, ensure you have met the following requirements:

- **Docker** 🐋: [Docker](https://docs.docker.com/get-docker/) is used for containerization. 
Ensure you have Docker installed. Any recent version should work, but it's recommended to use the latest stable release.
- **Node.js** 💚: [Node.js](https://nodejs.org/en/download/) for JavaScript runtime environment. During development used in version 18.17.1. 
- **Yarn** 🧶: [Yarn](https://yarnpkg.com/getting-started/install) for managing JavaScript packages. 
Yarn is preferred over npm for its performance and reliability in this project. During development used in version 1.22.19.

Optional for running Panther tests or local development: 
- **PHP**: [PHP](https://www.php.net/manual/en/install.php) version 8.1 or higher with certain php extensions
(see composer.json or Dockerfile), Symfony CLI (recommend version 5.5.2 or higher).
During development used PHP in version 8.2.13.
- **Composer**: [Composer](https://getcomposer.org/download/) version 2.x for managing PHP dependencies. 
During development used in version 2.5.5.
- **Google Chrome**: Any stable release should be fine. During development used in version 119.0.6045.159.
- **Chromedriver**: Ensure Chromedriver version matches the version of Google Chrome.

Note: The versions mentioned above are tested and known to work with this project. If you are using different versions, you may encounter compatibility issues.

### Setting Up for Development 🪄

1. **Clone the Repository** 🙋‍♂️🙋‍♂️:
   ```
   git clone https://gitlab.fit.cvut.cz/dundejan/seatmaster.git
   cd seatmaster
   ```

2. **Start the Development Environment** 🏃‍♂️:
   ```
   make up
   ```
    Note: The command is running yarn watch in the current terminal, so closing the terminal or 
          Ctrl+C will terminate the yarn watcher. If you prefer the yarn watch to run silently, feel free to modify 
          make up command to use for example `@nohup yarn watch > /dev/null 2>&1 &` instead of simple `@yarn watch`.


3. **Access the Application** 🕺:
   - The application should now be running on [localhost](http://localhost) (or a specified port).

### Running Tests 📈

- **Run Tests** (excluding Panther tests):
  ```
  make test
  ```

- **Run Panther Tests** 🐈‍⬛:
  Panther tests require a specific local setup:
    - **Composer**: Ensure you have Composer and run 
    - **PHP 8.1 or Higher**: Ensure you have PHP 8.1 or a higher version installed locally.
    - **Symfony Console**: The Symfony Console component is used for executing Doctrine commands.
    - **Google Chrome or an Alternative Browser**: Ensure you have Google Chrome (or the browser you intend to use with Panther) installed on your local machine. The current setup is tested with Google Chrome version 119.
    - **Chromedriver**: Make sure you have Chromedriver installed that matches the version of Google Chrome. For Chrome 119, use the corresponding version of Chromedriver.
    - **Environment Variables**: Set the path to Chromedriver and Google Chrome in the `.env.test` file. For example:
      ```
      PANTHER_CHROME_DRIVER_BINARY=/usr/bin/chromedriver
      PANTHER_CHROME_BINARY=/usr/bin/google-chrome
      ```
    - Change the browser for Panther tests by updating the `.env.test` file with the respective binary paths for the chosen browser.

  To run Panther tests:
  ```
  make test-panther
  ```

  Note: The commands within `test-panther` make use of the local PHP installation and Symfony console commands to interact with the Doctrine database, including creating, updating, and dropping the test database schema and loading fixtures.

### Linting and Static Analysis 🧪

- To lint JavaScript files 🧫:
  ```
  make eslint
  ```

- To perform PHP static analysis 🔬:
  ```
  make php-stan
  ```

### Cleaning Up 🧹

- To clean up generated files and clear caches:
  ```
  make clean
  ```

### Rebuilding the Environment 🏗️

- To rebuild the entire development environment:
  ```
  make rebuild
  ```
  Note: This is not rebuilding docker containers, this is just shutting the containers down, cleaning cache and again starting the containers.
        For container rebuild you will use `make docker-build`.

### Other 📜

All Makefile commands with descriptions can be seen with the command
```
make help
```
