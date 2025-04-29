<?php
// STUDENTS, FACULTY, OUTSIDE CLIENT
function renderNavbar($profile_image) {
    ?>
    <div class="navbar">
        <div class="navbar-items">
            <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 3.25rem; height: 3.25rem;">
            <img src="/gcc/img/wmsu-logo.png" alt="WMSU Logo" style="vertical-align: middle; width: 3.25rem; height: 3.25rem;">
            <a class="website" href="<?php
    switch ($_SESSION['role']) {
        case 'College Student':
            echo 'college.php';
            break;
        case 'High School Student':
            echo 'high-school.php';
            break;
        case 'Outside Client':
            echo 'outside.php';
            break;
        case 'Faculty':
            echo 'faculty.php';
            break;
        default:
            echo 'Role not identified!'; 
    }
    ?>">WMSU Guidance and Counseling Center</a>
        </div>
        <div class="navbar-content">
            <a href="<?php
                switch ($_SESSION['role']) {
                    case 'College Student':
                        echo 'college.php';
                        break;
                    case 'High School Student':
                        echo 'high-school.php';
                        break;
                    case 'Outside Client':
                        echo 'outside.php';
                        break;
                    case 'Faculty':
                        echo 'faculty.php';
                        break;
                    default:
                        echo 'Error 404'; 
                }
                ?>">Home</a>
            <?php
            $role = $_SESSION['role'];
            if ($role == 'Outside Client' || $role == 'Faculty') {
                // Show only a single Counseling link
                echo '<a href="../../shared/main/counseling.php" class="nav-link"><i class="fas fa-calendar-check"></i> Counseling</a>';
            } else {
                echo '
                <div class="dropdown">
                    <a href="#" class="dropbtn">Appointments <i class="fas fa-caret-down"></i></a>
                    <div class="dropdown-content">';
                        if ($role == 'High School Student') {
                            echo '<a href="../../../shared/main/counseling.php"><i class="fas fa-calendar-check"></i> Counseling</a>';
                            echo '<a href="../../../shared/main/assessment.php"><i class="fas fa-file-alt"></i> Assessments</a>';
                        } elseif ($role == 'College Student') {
                            echo '<a href="../../../shared/main/counseling.php"><i class="fas fa-calendar-check"></i> Counseling</a>';
                            echo '<a href="../../../shared/main/assessment.php"><i class="fas fa-file-alt"></i> Assessments</a>';
                            echo '<a href="../../../shared/main/shifting.php"><i class="fas fa-edit"></i> Shifting Exam</a>';
                        }
                echo '
                    </div>
                </div>';
            }
            ?>
            <div class="dropdown">
                <a href="#" class="dropbtn">About <i class="fas fa-caret-down"></i></a>
                <div class="dropdown-content">
                    <a href="/gcc/shared/sub-pages/contact-us.php"><i class="fas fa-envelope"></i> Contact Us</a>
                    <a href="/gcc/shared/sub-pages/about-us.php"><i class="fas fa-info-circle"></i> About Us</a>
                    <a href="/gcc/shared/sub-pages/our-team.php"><i class="fas fa-users"></i> Our Team</a>
                </div>
            </div>
            
            <?php
            $role = $_SESSION['role'];
            if ($role == 'Outside Client') {
                $profileLink = '../../shared/sub-pages/profile.php';
            } else {
                $profileLink = '../../../shared/sub-pages/profile.php';
            }
           ?>
         <img id="profileImage" class="profile-img"
             src="<?php echo $profile_image; ?>?v=<?php echo time(); ?>"
             alt="Profile"
             onclick="window.location.href='<?php echo $profileLink; ?>'">
        </div>
        <div class="sidebar-overlay"></div>
        <div class="burger-icon" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </div>
        <div class="sidebar" id="sidebar">
            <span class="close-btn" onclick="toggleSidebar()">
                <i class="fa-solid fa-xmark"></i>
            </span>
            <div class="menu-items">
                <a href="public-page.php"><i class="fas fa-home"></i>Home</a>
                <a class="h" data-section="Appointments"><i class="fas fa-calendar-check"></i> Appointments</a>
                <a class="h" data-section="Assessments"><i class="fas fa-file-alt"></i> Assessments</a>
                <a class="h" data-section="Shifting Exam"><i class="fas fa-edit"></i> Shifting Exam</a>
                <hr>
                <a href="contact.php"><i class="fas fa-envelope"></i> Contact Us</a>
                <a href="about.php"><i class="fas fa-info-circle"></i> About Us</a>
                <a href="team.php"><i class="fas fa-users"></i> Our Team</a>
                <hr>
                <a href="../auth/sign-in.php" class="logout"><i class="fas fa-sign-out-alt"></i>Sign In</a>
            </div>
        </div>
    </div>
    <?php
}

function counselingNavbar($profile_image) {
    ?>
    <div class="navbar">
        <div class="navbar-items">
            <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 3.25rem; height: 3.25rem;">
            <img src="/gcc/img/wmsu-logo.png" alt="WMSU Logo" style="vertical-align: middle; width: 3.25rem; height: 3.25rem;">
            <a class="website" href="<?php
 switch ($_SESSION['role']) {
    case 'Faculty':
        echo '../../client/inside/faculty/faculty.php';
        break;
    case 'College Student':
        echo '../../client/inside/student/college.php';
        break;
    case 'High School Student':
        echo '../../client/inside/student/high-school.php';
        break;
    case 'Outside Client':
        echo '../../client/outside/outside.php';
        break;
     default:
        echo 'Role not identified!';  
 }
 ?>">WMSU Guidance and Counseling Center</a>
        </div>
        <div class="navbar-content">
            <a href="<?php
 switch ($_SESSION['role']) {
    case 'Faculty':
        echo '../../client/inside/faculty/faculty.php';
        break;
    case 'College Student':
        echo '../../client/inside/student/college.php';
        break;
    case 'High School Student':
        echo '../../client/inside/student/high-school.php';
        break;
    case 'Outside Client':
        echo '../../client/outside/outside.php';
        break;
     default:
        echo 'Error 404';  
 }
 ?>">Home</a>
<?php
$role = $_SESSION['role'];
if ($role == 'Outside Client' || $role == 'Faculty') {
    // Show only a single Counseling link
    echo '<a href="../../shared/main/counseling.php" class="nav-link"><i class="fas fa-calendar-check"></i> Counseling</a>';
} else {
    // Show dropdown for others
    echo '
    <div class="dropdown">
        <a href="#" class="dropbtn">Appointments <i class="fas fa-caret-down"></i></a>
        <div class="dropdown-content">';
            if ($role == 'High School Student') {
                echo '<a href="../../shared/main/assessment.php"><i class="fas fa-file-alt"></i> Assessments</a>';
            } elseif ($role == 'College Student') {
                echo '<a href="../../shared/main/assessment.php"><i class="fas fa-file-alt"></i> Assessments</a>';
                echo '<a href="../../shared/main/shifting.php"><i class="fas fa-edit"></i> Shifting Exam</a>';
            }
    echo '
        </div>
    </div>';
}
?>
            <div class="dropdown">
                <a href="#" class="dropbtn">About <i class="fas fa-caret-down"></i></a>
                <div class="dropdown-content">
                    <a href="../../shared/sub-pages/contact-us.php"><i class="fas fa-envelope"></i> Contact Us</a>
                    <a href="../../shared/sub-pages/about-us.php"><i class="fas fa-info-circle"></i> About Us</a>
                    <a href="../../shared/sub-pages/our-team.php"><i class="fas fa-users"></i> Our Team</a>
                </div>
            </div>
            <?php
            $role = $_SESSION['role'];
            if ($role == 'Faculty' || $role == 'College Student' || $role == 'High School Student' || $role == 'Outside Client') {
                $profileLink = '../../shared/sub-pages/profile.php';
            }
           ?>
         <img id="profileImage" class="profile-img"
             src="<?php echo $profile_image; ?>?v=<?php echo time(); ?>"
             alt="Profile"
             onclick="window.location.href='<?php echo $profileLink; ?>'">
         </div>
        <div class="sidebar-overlay"></div>
        <div class="burger-icon" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </div>
        <div class="sidebar" id="sidebar">
            <span class="close-btn" onclick="toggleSidebar()">
                <i class="fa-solid fa-xmark"></i>
            </span>
            <div class="menu-items">
                <a href="public-page.php"><i class="fas fa-home"></i>Home</a>
                <a class="h" data-section="Appointments"><i class="fas fa-calendar-check"></i> Appointments</a>
                <a class="h" data-section="Assessments"><i class="fas fa-file-alt"></i> Assessments</a>
                <a class="h" data-section="Shifting Exam"><i class="fas fa-edit"></i> Shifting Exam</a>
                <hr>
                <a href="contact.php"><i class="fas fa-envelope"></i> Contact Us</a>
                <a href="about.php"><i class="fas fa-info-circle"></i> About Us</a>
                <a href="team.php"><i class="fas fa-users"></i> Our Team</a>
                <hr>
                <a href="../auth/sign-in.php" class="logout"><i class="fas fa-sign-out-alt"></i>Sign In</a>
            </div>
        </div>
    </div>
    <?php
}

function assessNavbar($profile_image) {
       ?>
       <div class="navbar">
           <div class="navbar-items">
               <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 3.25rem; height: 3.25rem;">
               <img src="/gcc/img/wmsu-logo.png" alt="WMSU Logo" style="vertical-align: middle; width: 3.25rem; height: 3.25rem;">
               <a class="website" href="<?php
    switch ($_SESSION['role']) {
        case 'College Student':
            echo '../../client/inside/student/college.php';
            break;
        case 'High School Student':
            echo '../../client/inside/student/high-school.php';
            break;
        default:
           echo 'Role not identified!';  
    }
    ?>">WMSU Guidance and Counseling Center</a>
           </div>
           <div class="navbar-content">
               <a href="<?php
    switch ($_SESSION['role']) {
        case 'College Student':
            echo '../../client/inside/student/college.php';
            break;
        case 'High School Student':
            echo '../../client/inside/student/high-school.php';
            break;
        default:
           echo 'Error 404';  
    }
    ?>">Home</a>
               <div class="dropdown">
                   <a href="#" class="dropbtn">Appointments <i class="fas fa-caret-down"></i></a>
                   <div class="dropdown-content">
                    <?php
                        $role = $_SESSION['role'];
                        if ($role == 'Outside Client') {
                            echo '<a href="../../shared/main/counseling.php"><i class="fas fa-calendar-check"></i> Counseling</a>';
                        } elseif ($role == 'High School Student') {
                            echo '<a href="../../shared/main/counseling.php"><i class="fas fa-calendar-check"></i> Counseling</a>';
                        } elseif ($role == 'Faculty') {
                            echo '<a href="../../shared/main/counseling.php"><i class="fas fa-calendar-check"></i> Counseling</a>';
                        } elseif ($role == 'College Student' || $role == 'Faculty') {
                            echo '<a href="../../shared/main/counseling.php"><i class="fas fa-calendar-check"></i> Counseling</a>';
                            echo '<a href="../../shared/main/shifting.php"><i class="fas fa-edit"></i> Shifting Exam</a>';
                        }
                    ?>
                </div>
               </div>
               <div class="dropdown">
                   <a href="#" class="dropbtn">About <i class="fas fa-caret-down"></i></a>
                   <div class="dropdown-content">
                       <a href="../../shared/sub-pages/contact-us.php"><i class="fas fa-envelope"></i> Contact Us</a>
                       <a href="../../shared/sub-pages/about-us.php"><i class="fas fa-info-circle"></i> About Us</a>
                       <a href="../../shared/sub-pages/our-team.php"><i class="fas fa-users"></i> Our Team</a>
                   </div>
               </div>
               <?php
            $role = $_SESSION['role'];
            if ($role == 'College Student' || $role == 'High School Student') {
                $profileLink = '../../shared/sub-pages/profile.php';
            }
           ?>
         <img id="profileImage" class="profile-img"
             src="<?php echo $profile_image; ?>?v=<?php echo time(); ?>"
             alt="Profile"
             onclick="window.location.href='<?php echo $profileLink; ?>'">
           </div>
           <div class="sidebar-overlay"></div>
           <div class="burger-icon" onclick="toggleSidebar()">
               <i class="fas fa-bars"></i>
           </div>
           <div class="sidebar" id="sidebar">
               <span class="close-btn" onclick="toggleSidebar()">
                   <i class="fa-solid fa-xmark"></i>
               </span>
               <div class="menu-items">
                   <a href="public-page.php"><i class="fas fa-home"></i>Home</a>
                   <a class="h" data-section="Appointments"><i class="fas fa-calendar-check"></i> Appointments</a>
                   <a class="h" data-section="Assessments"><i class="fas fa-file-alt"></i> Assessments</a>
                   <a class="h" data-section="Shifting Exam"><i class="fas fa-edit"></i> Shifting Exam</a>
                   <hr>
                   <a href="contact.php"><i class="fas fa-envelope"></i> Contact Us</a>
                   <a href="about.php"><i class="fas fa-info-circle"></i> About Us</a>
                   <a href="team.php"><i class="fas fa-users"></i> Our Team</a>
                   <hr>
                   <a href="../auth/sign-in.php" class="logout"><i class="fas fa-sign-out-alt"></i>Sign In</a>
               </div>
           </div>
       </div>
       <?php
   }

   function appointPageNavbar($profile_image) {
    ?>
    <div class="navbar">
        <div class="navbar-items">
            <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 3.25rem; height: 3.25rem;">
            <img src="/gcc/img/wmsu-logo.png" alt="WMSU Logo" style="vertical-align: middle; width: 3.25rem; height: 3.25rem;">
            <a class="website" href="<?php
 switch ($_SESSION['role']) {
     case 'College Student':
         echo '../../../client/inside/student/college.php';
         break;
     case 'High School Student':
         echo '../../../client/inside/student/high-school.php';
         break;
     case 'Outside Client':
        echo '../../../client/outside/outside.php';
        break;
     case 'Faculty':
         echo '../../../client/faculty/faculty.php';
         break;
     default:
        echo 'Role not identified!';  
 }
 ?>">WMSU Guidance and Counseling Center</a>
        </div>
        <div class="navbar-content">
            <a href="<?php
 switch ($_SESSION['role']) {
     case 'College Student':
         echo '../../../client/inside/student/college.php';
         break;
     case 'High School Student':
         echo '../../../client/inside/student/high-school.php';
         break;
     case 'Outside Client':
        echo '../../../client/outside/outside.php';
        break;
     case 'Faculty':
         echo '../../../client/faculty/faculty.php';
         break;
     default:
        echo 'Error 404';  
 }
 ?>">Home</a>
      <?php
      $role = $_SESSION['role'];
      if ($role == 'Outside Client' || $role == 'Faculty') {
          // Show only a single Counseling link
          echo '<a href="../../../shared/main/counseling.php" class="nav-link"><i class="fas fa-calendar-check"></i> Counseling</a>';
      } else {
          // Show dropdown for others
          echo '
          <div class="dropdown">
              <a href="#" class="dropbtn">Appointments <i class="fas fa-caret-down"></i></a>
              <div class="dropdown-content">';
                  if ($role == 'High School Student') {
                     echo '<a href="../../../shared/main/counseling.php" class="nav-link"><i class="fas fa-calendar-check"></i> Counseling</a>';
                      echo '<a href="../../../shared/main/assessment.php"><i class="fas fa-file-alt"></i> Assessments</a>';
                  } elseif ($role == 'College Student') {
                      echo '<a href="../../../shared/main/counseling.php" class="nav-link"><i class="fas fa-calendar-check"></i> Counseling</a>';
                      echo '<a href="../../../shared/main/assessment.php"><i class="fas fa-file-alt"></i> Assessments</a>';
                      echo '<a href="../../../shared/main/shifting.php"><i class="fas fa-edit"></i> Shifting Exam</a>';
                  }
          echo '
              </div>
          </div>';
      }
      ?>
            <div class="dropdown">
                <a href="#" class="dropbtn">About <i class="fas fa-caret-down"></i></a>
                <div class="dropdown-content">
                    <a href="../../../shared/sub-pages/contact-us.php"><i class="fas fa-envelope"></i> Contact Us</a>
                    <a href="../../../shared/sub-pages/about-us.php"><i class="fas fa-info-circle"></i> About Us</a>
                    <a href="../../../shared/sub-pages/our-team.php"><i class="fas fa-users"></i> Our Team</a>
                </div>
            </div>
            <?php
         $role = $_SESSION['role'];
         if ($role == 'College Student' || $role == 'High School Student') {
             $profileLink = '../../../shared/sub-pages/profile.php';
         } elseif ($role == 'Outside Client' || $role == 'Faculty') {
             $profileLink = '../../../shared/sub-pages/profile.php';
         }

        
        ?>
      <img id="profileImage" class="profile-img"
          src="<?php echo $profile_image; ?>?v=<?php echo time(); ?>"
          alt="Profile"
          onclick="window.location.href='<?php echo $profileLink; ?>'">
        </div>
        <div class="sidebar-overlay"></div>
        <div class="burger-icon" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </div>
        <div class="sidebar" id="sidebar">
            <span class="close-btn" onclick="toggleSidebar()">
                <i class="fa-solid fa-xmark"></i>
            </span>
            <div class="menu-items">
                <a href="public-page.php"><i class="fas fa-home"></i>Home</a>
                <a class="h" data-section="Appointments"><i class="fas fa-calendar-check"></i> Appointments</a>
                <a class="h" data-section="Assessments"><i class="fas fa-file-alt"></i> Assessments</a>
                <a class="h" data-section="Shifting Exam"><i class="fas fa-edit"></i> Shifting Exam</a>
                <hr>
                <a href="contact.php"><i class="fas fa-envelope"></i> Contact Us</a>
                <a href="about.php"><i class="fas fa-info-circle"></i> About Us</a>
                <a href="team.php"><i class="fas fa-users"></i> Our Team</a>
                <hr>
                <a href="../auth/sign-in.php" class="logout"><i class="fas fa-sign-out-alt"></i>Sign In</a>
            </div>
        </div>
    </div>
    <?php
}

   function shiftingNavbar($profile_image) {
    ?>
    <div class="navbar">
        <div class="navbar-items">
            <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 3.25rem; height: 3.25rem;">
            <img src="/gcc/img/wmsu-logo.png" alt="WMSU Logo" style="vertical-align: middle; width: 3.25rem; height: 3.25rem;">
            <a class="website" href="<?php
 switch ($_SESSION['role']) {
     case 'College Student':
         echo '../../client/inside/student/college.php';
         break;
     default:
        echo 'Role not identified!';  
 }
 ?>">WMSU Guidance and Counseling Center</a>
        </div>
        <div class="navbar-content">
            <a href="<?php
 switch ($_SESSION['role']) {
     case 'College Student':
         echo '../../client/inside/student/college.php';
         break;
     default:
        echo 'Error 404';  
 }
 ?>">Home</a>
            <div class="dropdown">
                <a href="#" class="dropbtn">Appointments <i class="fas fa-caret-down"></i></a>
                <div class="dropdown-content">
                 <?php
                     $role = $_SESSION['role'];
                     if ($role == 'College Student') {
                         echo '<a href="../../shared/main/counseling.php"><i class="fas fa-calendar-check"></i> Counseling</a>';
                         echo '<a href="../../shared/main/assessment.php"><i class="fas fa-file-alt"></i> Assessments</a>';
                    }
                 ?>
             </div>
            </div>
            <div class="dropdown">
                <a href="#" class="dropbtn">About <i class="fas fa-caret-down"></i></a>
                <div class="dropdown-content">
                    <a href="../../shared/sub-pages/contact-us.php"><i class="fas fa-envelope"></i> Contact Us</a>
                    <a href="../../shared/sub-pages/about-us.php"><i class="fas fa-info-circle"></i> About Us</a>
                    <a href="../../shared/sub-pages/our-team.php"><i class="fas fa-users"></i> Our Team</a>
                </div>
            </div>
            <?php
         $role = $_SESSION['role'];
         if ($role == 'College Student') {
             $profileLink = '../../shared/sub-pages/profile.php';
         }
        ?>
      <img id="profileImage" class="profile-img"
          src="<?php echo $profile_image; ?>?v=<?php echo time(); ?>"
          alt="Profile"
          onclick="window.location.href='<?php echo $profileLink; ?>'">
        </div>
        <div class="sidebar-overlay"></div>
        <div class="burger-icon" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </div>
        <div class="sidebar" id="sidebar">
            <span class="close-btn" onclick="toggleSidebar()">
                <i class="fa-solid fa-xmark"></i>
            </span>
            <div class="menu-items">
                <a href="public-page.php"><i class="fas fa-home"></i>Home</a>
                <a class="h" data-section="Appointments"><i class="fas fa-calendar-check"></i> Appointments</a>
                <a class="h" data-section="Assessments"><i class="fas fa-file-alt"></i> Assessments</a>
                <a class="h" data-section="Shifting Exam"><i class="fas fa-edit"></i> Shifting Exam</a>
                <hr>
                <a href="contact.php"><i class="fas fa-envelope"></i> Contact Us</a>
                <a href="about.php"><i class="fas fa-info-circle"></i> About Us</a>
                <a href="team.php"><i class="fas fa-users"></i> Our Team</a>
                <hr>
                <a href="../auth/sign-in.php" class="logout"><i class="fas fa-sign-out-alt"></i>Sign In</a>
            </div>
        </div>
    </div>
    <?php
}

   function aboutNavbar($profile_image) {
    ?>
    <div class="navbar">
        <div class="navbar-items">
            <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 3.25rem; height: 3.25rem;">
            <img src="/gcc/img/wmsu-logo.png" alt="WMSU Logo" style="vertical-align: middle; width: 3.25rem; height: 3.25rem;">
            <a class="website" href="<?php
                switch ($_SESSION['role']) {
                    case 'Faculty':
                        echo '../../client/faculty/faculty.php';
                        break;
                    case 'College Student':
                        echo '../../client/inside/student/college.php';
                        break;
                    case 'High School Student':
                        echo '../../client/inside/student/high-school.php';
                        break;
                    case 'Outside Client':
                        echo '../../client/outside/outside.php';
                        break;
                    default:
                        echo 'Role not identified!';
                }
            ?>">WMSU Guidance and Counseling Center</a>
        </div>
        <div class="navbar-content">
            <a href="<?php
                switch ($_SESSION['role']) {
                    case 'Faculty':
                        echo '../../client/faculty/faculty.php';
                        break;
                    case 'College Student':
                        echo '../../client/inside/student/college.php';
                        break;
                    case 'High School Student':
                        echo '../../client/inside/student/high-school.php';
                        break;
                    case 'Outside Client':
                        echo '../../client/outside/outside.php';
                        break;
                    default:
                        echo 'Error 404';
                }
            ?>">Home</a>

<?php
$role = $_SESSION['role'];
if ($role == 'Outside Client' || $role == 'Faculty') {
    // Show only a single Counseling link
    echo '<a href="../../shared/main/counseling.php" class="nav-link"><i class="fas fa-calendar-check"></i> Counseling</a>';
} else {
    // Show dropdown for others
    echo '
    <div class="dropdown">
        <a href="#" class="dropbtn">Appointments <i class="fas fa-caret-down"></i></a>
        <div class="dropdown-content">';
            if ($role == 'High School Student') {
                echo '<a href="../../shared/main/counseling.php"><i class="fas fa-calendar-check"></i> Counseling</a>';
                echo '<a href="../../shared/main/assessment.php"><i class="fas fa-file-alt"></i> Assessments</a>';
            } elseif ($role == 'College Student') {
                echo '<a href="../../shared/main/counseling.php"><i class="fas fa-calendar-check"></i> Counseling</a>';
                echo '<a href="../../shared/main/assessment.php"><i class="fas fa-file-alt"></i> Assessments</a>';
                echo '<a href="../../shared/main/shifting.php"><i class="fas fa-edit"></i> Shifting Exam</a>';
            }
    echo '
        </div>
    </div>';
}
?>
            <div class="dropdown">
                <a href="#" class="dropbtn">About <i class="fas fa-caret-down"></i></a>
                <div class="dropdown-content">
                    <a href="contact-us.php"><i class="fas fa-envelope"></i> Contact Us</a>
                    <a href="our-team.php"><i class="fas fa-users"></i> Our Team</a>
                </div>
            </div>

            <img id="profileImage" class="profile-img"
                src="<?php echo $profile_image; ?>?v=<?php echo time(); ?>"
                alt="Profile"
                onclick="window.location.href='../../shared/sub-pages/profile.php'">
        </div>

        <div class="sidebar-overlay"></div>
        <div class="burger-icon" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </div>
        <div class="sidebar" id="sidebar">
            <span class="close-btn" onclick="toggleSidebar()">
                <i class="fa-solid fa-xmark"></i>
            </span>
            <div class="menu-items">
                <a href="public-page.php"><i class="fas fa-home"></i>Home</a>
                <a class="h" data-section="Appointments"><i class="fas fa-calendar-check"></i> Appointments</a>
                <a class="h" data-section="Assessments"><i class="fas fa-file-alt"></i> Assessments</a>
                <a class="h" data-section="Shifting Exam"><i class="fas fa-edit"></i> Shifting Exam</a>
                <hr>
                <a href="contact.php"><i class="fas fa-envelope"></i> Contact Us</a>
                <a href="team.php"><i class="fas fa-users"></i> Our Team</a>
                <hr>
                <a href="../auth/sign-in.php" class="logout"><i class="fas fa-sign-out-alt"></i>Sign In</a>
            </div>
        </div>
    </div>
    <?php
}

function ourTeamNavbar($profile_image) {
    ?>
    <div class="navbar">
        <div class="navbar-items">
            <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 3.25rem; height: 3.25rem;">
            <img src="/gcc/img/wmsu-logo.png" alt="WMSU Logo" style="vertical-align: middle; width: 3.25rem; height: 3.25rem;">
            <a class="website" href="<?php
 switch ($_SESSION['role']) {
    case 'Faculty':
        echo '../../client/faculty/faculty.php';
        break;
    case 'College Student':
        echo '../../client/inside/student/college.php';
        break;
    case 'High School Student':
        echo '../../client/inside/student/high-school.php';
        break;
    case 'Outside Client':
        echo '../../client/outside/outside.php';
        break;
     default:
        echo 'Role not identified!';  
 }
 ?>">WMSU Guidance and Counseling Center</a>
        </div>
        <div class="navbar-content">
            <a href="<?php
 switch ($_SESSION['role']) {
    case 'Faculty':
        echo '../../client/faculty/faculty.php';
        break;
    case 'College Student':
        echo '../../client/inside/student/college.php';
        break;
    case 'High School Student':
        echo '../../client/inside/student/high-school.php';
        break;
    case 'Outside Client':
        echo '../../client/outside/outside.php';
        break;
     default:
        echo 'Error 404';  
 }
 ?>">Home</a>
<?php
$role = $_SESSION['role'];
if ($role == 'Outside Client' || $role == 'Faculty') {
    // Show only a single Counseling link
    echo '<a href="../../shared/main/counseling.php" class="nav-link"><i class="fas fa-calendar-check"></i> Counseling</a>';
} else {
    // Show dropdown for others
    echo '
    <div class="dropdown">
        <a href="#" class="dropbtn">Appointments <i class="fas fa-caret-down"></i></a>
        <div class="dropdown-content">';
            if ($role == 'High School Student') {
                echo '<a href="../../shared/main/counseling.php"><i class="fas fa-calendar-check"></i> Counseling</a>';
                echo '<a href="../../shared/main/assessment.php"><i class="fas fa-file-alt"></i> Assessments</a>';
            } elseif ($role == 'College Student') {
                echo '<a href="../../shared/main/counseling.php"><i class="fas fa-calendar-check"></i> Counseling</a>';
                echo '<a href="../../shared/main/assessment.php"><i class="fas fa-file-alt"></i> Assessments</a>';
                echo '<a href="../../shared/main/shifting.php"><i class="fas fa-edit"></i> Shifting Exam</a>';
            }
    echo '
        </div>
    </div>';
}
?>
            <div class="dropdown">
                <a href="#" class="dropbtn">About <i class="fas fa-caret-down"></i></a>
                <div class="dropdown-content">
                    <a href="contact-us.php"><i class="fas fa-envelope"></i> Contact Us</a>
                    <a href="about-us.php"><i class="fas fa-info-circle"></i> About Us</a>
                </div>
            </div>
            <img id="profileImage" class="profile-img"
                src="<?php echo $profile_image; ?>?v=<?php echo time(); ?>"
                alt="Profile"
                onclick="window.location.href='../../shared/sub-pages/profile.php'">                </div>
        <div class="sidebar-overlay"></div>
        <div class="burger-icon" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </div>
        <div class="sidebar" id="sidebar">
            <span class="close-btn" onclick="toggleSidebar()">
                <i class="fa-solid fa-xmark"></i>
            </span>
            <div class="menu-items">
                <a href="public-page.php"><i class="fas fa-home"></i>Home</a>
                <a class="h" data-section="Appointments"><i class="fas fa-calendar-check"></i> Appointments</a>
                <a class="h" data-section="Assessments"><i class="fas fa-file-alt"></i> Assessments</a>
                <a class="h" data-section="Shifting Exam"><i class="fas fa-edit"></i> Shifting Exam</a>
                <hr>
                <a href="contact.php"><i class="fas fa-envelope"></i> Contact Us</a>
                <a href="about.php"><i class="fas fa-info-circle"></i> About Us</a>
                <hr>
                <a href="../auth/sign-in.php" class="logout"><i class="fas fa-sign-out-alt"></i>Sign In</a>
            </div>
        </div>
    </div>
    <?php
}

function contactNavbar($profile_image) {
    ?>
    <div class="navbar">
        <div class="navbar-items">
            <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 3.25rem; height: 3.25rem;">
            <img src="/gcc/img/wmsu-logo.png" alt="WMSU Logo" style="vertical-align: middle; width: 3.25rem; height: 3.25rem;">
            <a class="website" href="<?php
 switch ($_SESSION['role']) {
    case 'Faculty':
        echo '../../client/faculty/faculty.php';
        break;
    case 'College Student':
        echo '../../client/inside/student/college.php';
        break;
    case 'High School Student':
        echo '../../client/inside/student/high-school.php';
        break;
    case 'Outside Client':
        echo '../../client/outside/outside.php';
        break;
     default:
        echo 'Role not identified!';  
 }
 ?>">WMSU Guidance and Counseling Center</a>
        </div>
        <div class="navbar-content">
            <a href="<?php
 switch ($_SESSION['role']) {
    case 'Faculty':
        echo '../../client/faculty/faculty.php';
        break;
    case 'College Student':
        echo '../../client/inside/student/college.php';
        break;
    case 'High School Student':
        echo '../../client/inside/student/high-school.php';
        break;
    case 'Outside Client':
        echo '../../client/outside/outside.php';
        break;
     default:
        echo 'Error 404';  
 }
 ?>">Home</a>
<?php
$role = $_SESSION['role'];
if ($role == 'Outside Client' || $role == 'Faculty') {
    // Show only a single Counseling link
    echo '<a href="../../shared/main/counseling.php" class="nav-link"><i class="fas fa-calendar-check"></i> Counseling</a>';
} else {
    // Show dropdown for others
    echo '
    <div class="dropdown">
        <a href="#" class="dropbtn">Appointments <i class="fas fa-caret-down"></i></a>
        <div class="dropdown-content">';
            if ($role == 'High School Student') {
                echo '<a href="../../shared/main/counseling.php"><i class="fas fa-calendar-check"></i> Counseling</a>';
                echo '<a href="../../shared/main/assessment.php"><i class="fas fa-file-alt"></i> Assessments</a>';
            } elseif ($role == 'College Student') {
                echo '<a href="../../shared/main/counseling.php"><i class="fas fa-calendar-check"></i> Counseling</a>';
                echo '<a href="../../shared/main/assessment.php"><i class="fas fa-file-alt"></i> Assessments</a>';
                echo '<a href="../../shared/main/shifting.php"><i class="fas fa-edit"></i> Shifting Exam</a>';
            }
    echo '
        </div>
    </div>';
}
?>
            <div class="dropdown">
                <a href="#" class="dropbtn">About <i class="fas fa-caret-down"></i></a>
                <div class="dropdown-content">
                    <a href="our-team.php"><i class="fas fa-envelope"></i> Our Team</a>
                    <a href="about-us.php"><i class="fas fa-info-circle"></i> About Us</a>
                </div>
            </div>
            <img id="profileImage" class="profile-img"
                src="<?php echo $profile_image; ?>?v=<?php echo time(); ?>"
                alt="Profile"
                onclick="window.location.href='../../shared/sub-pages/profile.php'">                </div>
        <div class="sidebar-overlay"></div>
        <div class="burger-icon" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </div>
        <div class="sidebar" id="sidebar">
            <span class="close-btn" onclick="toggleSidebar()">
                <i class="fa-solid fa-xmark"></i>
            </span>
            <div class="menu-items">
                <a href="public-page.php"><i class="fas fa-home"></i>Home</a>
                <a class="h" data-section="Appointments"><i class="fas fa-calendar-check"></i> Appointments</a>
                <a class="h" data-section="Assessments"><i class="fas fa-file-alt"></i> Assessments</a>
                <a class="h" data-section="Shifting Exam"><i class="fas fa-edit"></i> Shifting Exam</a>
                <hr>
                <a href="our-team.php"><i class="fas fa-envelope"></i> Our Team</a>
                <a href="about.php"><i class="fas fa-info-circle"></i> About Us</a>
                <hr>
                <a href="../auth/sign-in.php" class="logout"><i class="fas fa-sign-out-alt"></i>Sign In</a>
            </div>
        </div>
    </div>
    <?php
}
?>