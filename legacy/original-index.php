<?php

// Database connection

$conn = mysqli_connect('localhost', 'root', '', 'portofoliocontact_db') or die('Connection failed: ' . mysqli_connect_error());

if (isset($_POST['send'])) {
    // Escape user inputs for security
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $number = mysqli_real_escape_string($conn, $_POST['number']);
    $msg = mysqli_real_escape_string($conn, $_POST['message']);

    // Check for existing message 
    $select_message = mysqli_query($conn, "SELECT * FROM `contact_form` WHERE name = '$name' AND email = '$email' AND number = '$number' AND message = '$msg'") or die('Query failed: ' . mysqli_error($conn));

    if (mysqli_num_rows($select_message) > 0) {
        $message[] = 'Message already sent';
    } else {
        // Insert new message    
        mysqli_query($conn, "INSERT INTO `contact_form`(name, email, number, message) VALUES ('$name', '$email', '$number', '$msg')") or die('Query failed: ' . mysqli_error($conn));
        $message[] = 'Message sent successfully!';
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Complete Portfolio Website-Larisa</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/00273616bd.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <!-- aos css link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <?php
    // Display messages if any
    if (isset($message)) {
        foreach ($message as $msg) {
            echo '  
            <div class="message" data-aos="zoom-out"> 
                <span>' . $msg . '</span> 
                <i class="fas fa-times" onclick="this.parentElement.remove();"></i>           
            </div>            
            ';
        }
    }

    ?>

    <header class="header">
        <div id="menu-btn" class="fa-solid fa-bars" style="color: #FFD43B;"></div>
        <a href="#home" class="logo">Portfolio</a>
        <nav class="navbar">
            <a href="#home" class="active">home</a>
            <a href="#about">about</a>
            <a href="#services">services</a>
            <a href="#portofolio">portfolio</a>
            <a href="#contact">contact</a>
        </nav>
        <div class="follow">
            <a href="#" class="fab fa-facebook"></a>
            <a href="#" class="fab fa-instagram"></a>
            <a href="#" class="fab fa-linkedin"></a>
            <a href="#" class="fab fa-github"></a>
        </div>
    </header>

    <!-- Home Section -->
    <section class="home" id="home">
        <div class="container d-flex align-items-center flex-wrap gap-3">
            <div class="image col-md-6" data-aos="fade-right">
                <img src="images/user-img.jpg" class="img-fluid" alt="User Image">
            </div>
            <div class="content col-md-6">
                <h3 data-aos="fade-up">[ʜᴇʟʟᴏ, ɪ'ᴍ ʟᴀʀɪꜱᴀ]</h3>
                <span data-aos="fade-up" class="d-inline-block bg-warning text-dark p-3 my-3">ꜰᴜʟʟ-ꜱᴛᴀᴄᴋ ᴅᴇᴠᴇʟᴏᴘᴇʀ ᴀɴᴅ
                    🅲🆁🅴🅰🆃🅸🆅🅴
                    ᴇﾒᴘʟᴏʀᴇʀ</span>
                <a data-aos="fade-up" href="#about" class="btn btn-outline-dark">About me</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" id="about">
        <div class="container">
            <h1 class="heading" data-aos="fade-up"><span>Biography</span></h1>
            <div class="biography">
                <div class="row justify-content-center">
                    <div data-aos="fade-up" class="list col-md-4 text-center">
                        <h3>Web Development</h3>
                        <h3>Backend Development</h3>
                        <h3>Database Management</h3>
                        <h3>Project Development and Management</h3>
                        <h3>Application Development</h3>
                        <h3>Communication and Teamwork</h3>
                    </div>
                    <p data-aos="fade-up" class="text-center">Hello! I'm Larisa, a dedicated programmer with a passion
                        for crafting
                        efficient and user-friendly
                        digital solutions. Proficient in various programming languages and backend development, I excel
                        at transforming complex challenges into seamless experiences. I'm always eager to learn and
                        embrace new technologies. Below are the services I offer and the skills I bring to the table.
                    </p>
                    <p data-aos="fade-up" class="text-center"> On a quest to become a programming wizard, I've recently
                        leveled up with a
                        GF2 in Data
                        Technology, focusing on programming. My mission? To conquer the backend development world
                        while keeping my humor alive. Alongside my delightful 4-year-old daughter, we’re dreaming of
                        moving from Langeskov to a bustling city, where new adventures await us both.When not
                        wrangling code or toys, I dive into psychology, massage, and bonsai. I'm also a terrarium
                        and painting enthusiast. These hobbies keep me balanced and ready for new challenges with a
                        smile. Whether debugging code or nurturing a rare plant, I thrive on growth, creativity, and
                        a dash of fun!</p>
                </div>
                <div class="bio row justify-content-center mt-4">
                    <div class="col-md-6">
                        <h3 data-aos="zoom-in"><span>Name: </span> Larisa Elena</h3>
                        <h3 data-aos="zoom-in"><span>Email: </span> larisaeb0289@hmail.com</h3>
                        <h3 data-aos="zoom-in"><span>Address: </span> Langeskov, Denmark</h3>
                        <h3 data-aos="zoom-in"><span>Phone: </span> +4550269011</h3>
                        <h3 data-aos="zoom-in"><span>Age: </span> 35 years</h3>
                        <h3 data-aos="zoom-in"><span>Experience: </span> 1+ year of experience</h3>
                    </div>
                </div>
                <a href="#" class="btn btn-outline-dark mt-3" data-aos="fade-up">Download CV</a>
            </div>

            <div class="skills mt-5" data-aos="fade-up">
                <h1 class="heading"><span>Skills</span></h1>
                <div data-aos="fade-right" class="progress mt-2">
                    <div class="progress-bar bg-warning text-dark" role="progressbar" style="inline-size: 70%;"
                        aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                        <h3><span>HTML</span> <span>75%</span></h3>
                    </div>
                </div>

                <div data-aos="fade-left" class="progress mt-2">
                    <div class="progress-bar bg-warning text-dark" role="progressbar" style="inline-size: 70%;"
                        aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                        <h3><span>CSS</span> <span>70%</span></h3>
                    </div>
                </div>

                <div data-aos="fade-right" class="progress mt-2">
                    <div class="progress-bar bg-warning text-dark" role="progressbar" style="inline-size: 70%;"
                        aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                        <h3><span>PHP</span> <span>85%</span></h3>
                    </div>
                </div>

                <div data-aos="fade-left" class="progress mt-2">
                    <div class="progress-bar bg-warning text-dark" role="progressbar" style="inline-size: 70%;"
                        aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                        <h3><span>Python</span> <span>55%</span></h3>
                    </div>
                </div>

                <div data-aos="fade-right" class="progress mt-2">
                    <div class="progress-bar bg-warning text-dark" role="progressbar" style="inline-size: 70%;"
                        aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                        <h3><span>C#</span> <span>45%</span></h3>
                    </div>
                </div>

                <div data-aos="fade-left" class="progress mt-2">
                    <div class="progress-bar bg-warning text-dark" role="progressbar" style="inline-size: 70%;"
                        aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                        <h3><span>JavaScript</span> <span>80%</span></h3>
                    </div>
                </div>

                <div data-aos="fade-right" class="progress mt-2">
                    <div class="progress-bar bg-warning text-dark" role="progressbar" style="inline-size: 70%;"
                        aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                        <h3><span>Bootsrap</span> <span>80%</span></h3>
                    </div>
                </div>

                <div data-aos="fade-left" class="progress mt-2">
                    <div class="progress-bar bg-warning text-dark" role="progressbar" style="inline-size: 70%;"
                        aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                        <h3><span>Laravel</span> <span>65%</span></h3>
                    </div>
                </div>

                <div data-aos="fade-right" class="progress mt-2">
                    <div class="progress-bar bg-warning text-dark" role="progressbar" style="inline-size: 70%;"
                        aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                        <h3><span>MySql</span> <span>75%</span></h3>
                    </div>
                </div>

                <div data-aos="fade-left" class="progress mt-2">
                    <div class="progress-bar bg-warning text-dark" role="progressbar" style="inline-size: 70%;"
                        aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                        <h3><span>Visual Studio</span> <span>95%</span></h3>
                    </div>
                </div>

                <div data-aos="fade-right" class="progress mt-2">
                    <div class="progress-bar bg-warning text-dark" role="progressbar" style="inline-size: 70%;"
                        aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                        <h3><span>GitHub</span> <span>65%</span></h3>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services section -->
    <section class="services" id="services">
        <h1 data-aos="zoom-in" class="heading"><span>services</span></h1>
        <div class="box-container">
            <div class="box" data-aos="zoom-in">
                <i class="fas fa-code"></i>
                <h3>Programming Skills</h3>
                <p>As a skilled programmer, I work with languages like PHP, Python, and SQL to create
                    websites and solve tricky problems. I also use C#, along with CSS, HTML, and Bootstrap,
                    to make web pages that are both attractive and easy to use.</p>
            </div>

            <div class="box" data-aos="zoom-in">
                <i class="fas services fa-layer-group"></i>
                <h3>Backend Development</h3>
                <p>Behind-the-Scenes Work: I ensure websites run smoothly even with
                    lots of visitors, writing code that handles data quickly and safely.</p>
            </div>

            <div class="box" data-aos="zoom-in">
                <i class="fab fa-bootstrap"></i>
                <h3>Web Development</h3>
                <p>Connecting Front and Back: My web development
                    skills connect the design with the working parts, using modern tools to improve how
                    users interact with the site.</p>
            </div>

            <div class="box" data-aos="zoom-in">
                <i class="fa-solid fa-database"></i>
                <h3>Database Management</h3>
                <p>With a strong foundation in database management, I set up and
                    maintain fast and reliable databases, optimizing data access and retrieval.</p>
            </div>

            <div class="box" data-aos="zoom-in">
                <i class="fa-solid fa-terminal"></i>
                <h3>Application Development</h3>
                <p>End-to-End Projects:
                    Work on all parts of a project, ensuring everything fits together and follow best
                    practices to ensure quality and reliability.</p>
            </div>
        </div>
    </section>

    <!-- Portofolio section -->
    <section class="portofolio" id="portofolio">
        <h1 class="heading" data-aos="zoom-in"> <span>portofolio</span></h1>
        <br>
        <h2 data-aos="zoom-in">projects</h2>
        <br><br>
        <div class="box-container">
            <div class="box" data-aos="zoom-in">
                <img src="images/img1.jpg" alt="">
                <h3>GreenHub</h3>
                <p>A website to serve as a community-driven forum promoting eco-friendly
                    and sustainable shopping at thrift stores using PHP, CSS, JavaScript, HTML, and
                    MySQL.</p>
            </div>

            <div class="box" data-aos="zoom-in">
                <img src="images/img2.png" alt="">
                <h3>KaosTek</h3>
                <p>KaosTek uses an old database that only consists of one table. The table
                    contains various information about the company's customers, such as names, addresses and
                    orders. This means that all the information is collected in one table, which makes it
                    difficult to update and maintain customer data correctly. I have decided to develop a new
                    data base that is better structured and a website that displays an overview of all products.
                    The products are sorted from the cheapest to the most expensive and database is normalize to
                    2nd normal form.</p>
            </div>
        </div>

        <h2 data-aos="zoom-in">Skills & Tools Development</h2>
        <br><br>
        <div class="box-container">
            <div class="box" data-aos="zoom-in">
                <img src="images/skills1.jpg" alt="">
                <h3>Programming Languages</h3>
                <p>Mastery of PHP, Python, C#, and SQL, allowing me to tackle various
                    programming challenges with confidence.</p>
            </div>

            <div class="box" data-aos="zoom-in">
                <img src="images/skills2.jpg" alt="">
                <h3>Web Development Technologies</h3>
                <p>Expertise in HTML, CSS, and Bootstrap, enabling the creation of
                    responsive and user-friendly interfaces.</p>
            </div>

            <div class="box" data-aos="zoom-in">
                <img src="images/skills3.jpg" alt="">
                <h3>Database Management</h3>
                <p>Advanced skills in SQL for efficient data storage, retrieval,
                    and manipulation, crucial for backend development.</p>
            </div>
        </div>
    </section>

    <!--contact section-->
    <section class="contact" id="contact">

        <h1 class="heading" data-aos="zoom-in"> <span>contact me</span> </h1>
        <form action="" method="post">
            <div class="flex">
                <input data-aos="zoom-in" type="text" name="name" placeholder="enter your name" class="box" required>
                <input data-aos="zoom-in" type="email" name="email" placeholder="enter your email" class="box" required>
            </div>
            <input data-aos="zoom-in" type="number" min="0" name="number" placeholder="enter your number" class="box" required>
            <textarea data-aos="zoom-in" name="message" class="box" required placeholder="enter your message" cols="30"
                rows="10"></textarea>
            <input data-aos="zoom-in" type="submit" value="send message" name="send" class="btn">
        </form>

        <div class="box-container" data-aos="zoom-in">
            <div class="box" data-aos="zoom-in">
                <i class="fas fa-phone"></i>
                <h3>phone</h3>
                <p>+4512-334-569</p>
            </div>

            <div class="box" data-aos="zoom-in">
                <i class="fas fa-envelope"></i>
                <h3>email</h3>
                <p>larisaeb0289@gmail.com</p>
            </div>

            <div class="box" data-aos="zoom-in">
                <i class="fas fa-map-marker-alt"></i>
                <h3>address</h3>
                <p>Nyborgvej 4C</p>
            </div>
        </div>
    </section>

    <div class="credit"> &copy; copyright @ <?php echo date('Y'); ?> by <span>ꜰᴜʟʟ-ꜱᴛᴀᴄᴋ ᴅᴇᴠᴇʟᴏᴘᴇʀ ᴀɴᴅ 🅲🆁🅴🅰🆃🅸🆅🅴
            ᴇﾒᴘʟᴏʀᴇʀ</span> </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <!-- Custom JS -->
    <script src="script/script.js"></script>

    <!-- aos JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

    <script>
        AOS.init({
            duration: 1000,
            delay: 300
        });
    </script>

</body>

</html>