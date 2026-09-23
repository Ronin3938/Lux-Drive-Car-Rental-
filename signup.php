<?php
// Start the session only once at the very top
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'dbconnect.php';


$errors = [];
$success = false;

// Initialize values
$first_name = $last_name = $email = '';
$gender = $birthdate = $phone = '';
$city = $street = $home_no = '';
$latitude = $longitude = '';
$profile_picture = null;
$password = $confirm_password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect all inputs
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $gender     = $_POST['gender'];
    $birthdate  = $_POST['birthdate'];
    $phone      = $_POST['phone'];
    $city       = $_POST['city'];
    $street     = $_POST['street'];
    $home_no    = $_POST['home_no'];
    $latitude   = $_POST['latitude'];
    $longitude  = $_POST['longitude'];
    $password   = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Email uniqueness check
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Email already exists.";
    }

    // Password check
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match!";
    }

    // Handle profile picture
    if (!empty($_FILES['profile_picture']['name'])) {
        $uploadDir = "uploads/profile_pics/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $fileName = time() . "_" . basename($_FILES['profile_picture']['name']);

        // Use full path for move_uploaded_file to avoid issues
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
            $profile_picture = $targetFilePath;
        } else {
            $errors[] = "Failed to upload profile picture.";
        }
    }

    // If no errors → insert and redirect
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $full_name = $first_name . " " . $last_name;
        $address   = $home_no . ", " . $street . ", " . $city;

        $stmt = $pdo->prepare("
            INSERT INTO users 
            (name,email,password,phone,address,latitude,longitude,gender,birthdate,profile_picture,role,created_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW())
        ");

        $stmt->execute([
            $full_name,
            $email,
            $hashed_password,
            $phone,
            $address,
            $latitude,
            $longitude,
            $gender,
            $birthdate,
            $profile_picture, // Pass the variable with the correct path
            'customer'
        ]);

        $user_id = $pdo->lastInsertId();

        // Save user data to session before redirecting
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $full_name;
        $_SESSION['profile_picture'] = $profile_picture;
        $_SESSION['success_message'] = "Signup successful!";

        header("Location: homepage.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Signup - Car Rental</title>
    <link rel="stylesheet" href="css/signup.css">
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>

<body>

    <div class="left-panel">
        <form id="signupForm" method="post" enctype="multipart/form-data">
            <div class="step" id="step1">
                <h2>Step 1: Basic Info</h2>
                <input type="text" name="first_name" placeholder="First Name" value="<?= htmlspecialchars($first_name) ?>" required>
                <input type="text" name="last_name" placeholder="Last Name" value="<?= htmlspecialchars($last_name) ?>" required>
                <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email) ?>" required>
                <button type="button" class="btn btn-primary" onclick="nextStep()">Next</button>
            </div>

            <div class="step" id="step2" style="display:none;">
                <h2>Step 2: Personal Info</h2>
                <select name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male" <?= $gender === 'Male' ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= $gender === 'Female' ? 'selected' : '' ?>>Female</option>
                    <option value="Other" <?= $gender === 'Other' ? 'selected' : '' ?>>Other</option>
                </select>
                <input type="date" name="birthdate" placeholder="Birthdate" value="<?= htmlspecialchars($birthdate) ?>" required>
                <input type="text" name="phone" placeholder="Phone Number" value="<?= htmlspecialchars($phone) ?>" required>
                <input type="text" name="city" placeholder="City" value="<?= htmlspecialchars($city) ?>" required>
                <input type="text" name="street" placeholder="Street" value="<?= htmlspecialchars($street) ?>" required>
                <input type="text" name="home_no" placeholder="Home No" value="<?= htmlspecialchars($home_no) ?>" required>
                <button type="button" class="btn btn-secondary" onclick="openMap()">Choose Location</button>
                <div id="locationModal" class="modal">
                    <div class="modal-content">
                        <span class="close" onclick="closeMap()">&times;</span>
                        <div id="map" style="width:100%;height:300px;"></div>
                        <input type="hidden" name="latitude" id="latitude" value="<?= htmlspecialchars($latitude) ?>">
                        <input type="hidden" name="longitude" id="longitude" value="<?= htmlspecialchars($longitude) ?>">
                    </div>
                </div>
                <button type="button" class="btn btn-primary" onclick="prevStep()">Previous</button>
                <button type="button" class="btn btn-primary" onclick="nextStep()">Next</button>
            </div>

            <div class="step" id="step3" style="display:none;">
                <h2>Step 3: Profile Picture</h2>
                <input type="file" name="profile_picture" id="profile_picture">
                <img id="preview" src="#" alt="Preview" style="display:none;max-width:150px;margin-top:10px;">
                <button type="button" class="btn btn-primary" onclick="prevStep()">Previous</button>
                <button type="button" class="btn btn-secondary" onclick="skipStep3()">Skip</button>
                <button type="button" class="btn btn-primary" onclick="nextStep()">Next</button>
            </div>

            <div class="step" id="step4" style="display:none;">
                <h2>Step 4: Set Password</h2>
                <input type="password" name="password" placeholder="Password" id="password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" id="confirm_password" required>
                <div class="checkbox-group">
                    <input type="checkbox" id="showPasswordToggle" onclick="togglePassword()">
                    <label for="showPasswordToggle">Show Password</label>
                </div>
                <button type="button" class="btn btn-primary" onclick="prevStep()">Previous</button>
                <button type="button" class="btn btn-primary" onclick="nextStep()">Sign Up</button>
                <div class="checkbox-group">
                    <input type="checkbox" id="acceptTerms" name="acceptTerms" required>
                    <label for="acceptTerms">I accept the <a href="rental_terms.php" target="_blank">Terms, Conditions</a> and <a href="privacy_policy.php" target="_blank">Policy</a> of Lux Drive</label>
                </div>
            </div>
        </form>
    </div>

    <div class="right-panel">
        <div class="brand">Lux Drive</div>
        <div class="tagline">Luxury. Comfort. Reliability.</div>
        <div class="highlight-box">
            <h1>Welcome</h1>
            <p>Book your ride easily with <b>Lux Drive</b>. Choose from luxury, SUVs, or economy cars. Travel smarter with our trusted service.</p>
        </div>
    </div>

    <div id="messageModal" class="modal">
        <div class="modal-content">
            <p id="messageText"></p>
            <button class="btn" onclick="closeMessagePopup()">OK</button>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="msg"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
    <?php endif; ?>

    <script src="javascript/signup.js"></script>
</body>

</html>