
# Project: Terminators
# Team: HaveYouAll
By: James M. -- Michael O.

This application is a simple forum website for the topic of games. We have a homepage where users are able to 
view forums and some live data but do not have access to post of make changes until they are logged in. There is 
functionality of a sign up / login page with incorporating a database.

## Project Requirements

1. Separates all database/business logic using the MVC pattern.
2. Routes all URLs and leverages a templating language using the Fat-Free framework.
3. Has a clearly defined database layer using PDO and prepared statements.
4. Data can be added and viewed.
5. Has a history of commits from both team members to a Git repository. Commits are clearly commented.
6. Uses OOP, and utilizes multiple classes, including at least one inheritance relationship.
7. Contains full Docblocks for all PHP files and follows PEAR standards.
8. Has full validation on the server side through PHP.
9. All code is clean, clear, and well-commented. DRY (Don't Repeat Yourself) is practiced.
10. Your submission shows adequate effort for a final project in a full-stack web development course.

## Implementation

1. All files are placed appropriately into model, view and controller directories.
2. All routes are defined in index.php, and all views leverage templating to minimize redundancy. views/home.html and views/search.html both make extensive use of templating, reusing the same template to display posts via views/post_template.html.
3. All code that interacts directly with the database is contained within model/db.php. All queries are done through PDO and prepared statements.
4. Data can be added in the form of user accounts, posts and reports. Posts may be viewed on the home page and throughout the application, reports may be viewed from an administrator dashboard page (only accessible while logged in with an admin account)
5. The commit history may be viewed on the github repository. Each of us contributed.
6. OOP is used, all of the classes may be found within the model directory. The Admin class (model/admin.php) extends the User class (model/user.php).
7. All files, classes and functions contain docblock comments. PEAR standards are utilized throughout the PHP code.
8. Forms use serverside validation, implemented in model/validation.php and controller/controller.php.
9. The code is clean, and we received very positive feedback during the code review. Comments are utilized everywhere we felt they were appropriate. DRY is practiced throughout the project code, and methods (ie readFormInput in controller/controller.php) are used to minimize redundancy.
10. We believe this project is a logical next step after the project from sdev305. We utilized much of what we learned in both that class and this one.

--

Throughout this project, we were able to maintain MVC principles by separating database side of the project from 
the rest and keeping it secure. We also made use of the fat free framework by adapting to the principles 
we were taught throughout the quarter. One of the bigger things from this list was the templating and routing portion.
We were able to implement this which allows for professionalism and cleanliness of code and files. Git version control
assisted in this as well. In this project, data can be viewed in terms of post, replies, likes, etc. It cna also be
added by the user creating a post, leaving comments, likes, etc. Git was used in this project and we were both able 
to post and use it without serious conflicts. In this project we were able to use OOP in terms of the posts that we
receive. Inheritance was used for our login portions for gathering user info and storing. We also tried to follow PEAR
standards to keep everything in check and legible / understandable for developers in terms of PHP legibility and format.
We were able to accomplish validation through PHP for our forms and login. We applied this as we learned validation in
class and it all seems to work well. Code is commented well but there may still be some slight changes.
Additionally, account passwords are hashed to improve security.