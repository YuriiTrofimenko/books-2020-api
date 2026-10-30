# Common Books API (Backend)

[![PHP](https://img.shields.io/badge/PHP-7.x/8.x-777BB4.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+/8.0+-4479A1.svg)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Legacy_Prototype_(Seeking_Maintainers)-yellow.svg)](-)

Backend REST API for the **Common Books** web application.

## 📖 About the Project

**Common Books** is a platform for maximally free, address-based, and unconditional exchange of physical books between users. The project was created as an alternative to commercial sales, overcrowded libraries, and chaotic bookcrossing. It allows users to catalog their home libraries and pass books to specific people (temporarily "lend for reading" or permanently "give away"), preserving human interaction and trust.

This repository contains the **server-side (Backend)** of the application, implementing a REST API for database interaction and the frontend client.

🔗 **Frontend Repository (Vue 3):** [books-2020-vue3-dev](https://github.com/YuriiTrofimenko/books-2020-vue3-dev.git)

## 🏗 Architecture & Tech Stack

- **Language:** PHP
- **Database:** MySQL / MariaDB
- **Architecture:** RESTful API
- **Authentication:** Google OAuth integration (via frontend)
- **Hosting (historical):** Developed for deployment on a standard LAMP stack.

## ⚠️ Current Project Status

This repository contains a **working but frozen prototype**. Development was paused due to life circumstances, and in its current form, the project requires modernization before a full production launch.

**What needs to be done (Roadmap for contributors):**

1. 🔒 **Security:** Code audit, protection against SQL injection, XSS, and CSRF. Update outdated dependencies.
2. 🔄 **Modernization:** Migrate code to current PHP standards (8.x), implement modern routing and patterns (e.g., transition to a lightweight framework like Slim or Laravel, if the community decides it's necessary).
3. 🐳 **Containerization:** Create `Dockerfile` and `docker-compose.yml` for quick local environment setup (PHP + MySQL) for new developers.
4. 📝 **API Documentation:** Generate current Swagger/OpenAPI specification for convenient frontend developer work.
5. 🧪 **Testing:** Write unit and integration tests for main endpoints.

## 🚀 Local Setup (for developers)

*Instructions will be supplemented. Currently, running requires:*

1. Install a local server (e.g., OpenServer, XAMPP, or Docker).
2. Create a MySQL database and import the schema (`schema.sql` file will be added).
3. Configure the `config.php` file (or `.env` after refactoring), specifying database access credentials.
4. Run the built-in PHP server or configure a virtual host.

(If you're ready to take on the task of writing full deployment instructions via Docker — your Pull Request will be met with immense gratitude!)

## 🤝 How to Contribute

This project is created not for profit, but for the development of a culture of mutual aid and free knowledge exchange (HumanBuilding). We seek developers who want to gain real experience, improve their skills, and leave a mark on a useful open project.

1. **Fork** this repository.
2. Create a branch for your task: `git checkout -b feature/improve-security` or `fix/api-endpoint`.
3. Make changes and commit them: `git commit -m 'Fix: improved input data validation'`.
4. Push changes to your fork: `git push origin feature/improve-security`.
5. Open a **Pull Request** with a detailed description of your changes.

If you have questions or architectural suggestions, don't hesitate to open **Issues** or contact the author directly.

## 📜 Contacts

- **Author:** Yurii Trofimenko
- **Email:** <tyaa10@gmail.com>
- **Telegram:** [@YuriiTrofimenko](https://t.me/YuriiTrofimenko)
- **Personal VK:** [vk.com/yuriitrofimenko](https://vk.ru/yuriitrofimenko)
- **Personal FB:** [facebook.com/yuriitrofimenko1986](https://www.facebook.com/yuriitrofimenko1986)
- **HB Community VK:** [vk.com/humanbuilders](https://vk.ru/humanbuilders)
- **HB Community FB:** [facebook.com/groups/150758748845784](https://www.facebook.com/groups/150758748845784)

---
*"We cannot wait for favors from nature. Taking them from it is our task" (I.V. Michurin). In the context of HumanBuilding: we cannot wait for corporations to make knowledge exchange convenient. We create these tools ourselves.*
