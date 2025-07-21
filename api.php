<?php
// Set header for JSON response
header('Content-Type: application/json');

// Allow cross-origin requests for development purposes (remove in production)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Define data file paths
$userDataFile = 'data/user.json';
$questionsDataFile = 'data/pertanyaan.json';
$answersDataFile = 'data/jawaban.json';
$lkpdUploadDir = 'uploads/';

// Ensure the data and uploads directories exist
if (!is_dir('data')) {
    mkdir('data', 0777, true);
}
if (!is_dir($lkpdUploadDir)) {
    mkdir($lkpdUploadDir, 0777, true);
}

// Initialize JSON files if they don't exist
if (!file_exists($userDataFile)) {
    file_put_contents($userDataFile, json_encode([['username' => 'siswa', 'password' => 'password123']], JSON_PRETTY_PRINT));
}
if (!file_exists($questionsDataFile)) {
    // Sample questions
    $sampleQuestions = [
        [
            "id" => "1",
            "type" => "mcq",
            "question" => "Apa bunyi Hukum Newton I?",
            "options" => [
                "a" => "Percepatan berbanding lurus dengan gaya",
                "b" => "Setiap aksi ada reaksi",
                "c" => "Benda cenderung mempertahankan keadaannya",
                "d" => "Gaya adalah massa dikali percepatan"
            ],
            "answer" => "c"
        ],
        [
            "id" => "2",
            "type" => "mcq",
            "question" => "Rumus Hukum Newton II adalah...",
            "options" => [
                "a" => "F = m/a",
                "b" => "F = m * a",
                "c" => "F = a/m",
                "d" => "F = m + a"
            ],
            "answer" => "b"
        ],
        [
            "id" => "3",
            "type" => "mcq",
            "question" => "Jika Anda mendorong dinding, dinding akan memberikan gaya yang sama besar namun berlawanan arah. Ini adalah contoh Hukum Newton ke-",
            "options" => [
                "a" => "I",
                "b" => "II",
                "c" => "III",
                "d" => "IV"
            ],
            "answer" => "c"
        ],
        [
            "id" => "4",
            "type" => "mcq",
            "question" => "Satuan dari gaya dalam Sistem Internasional (SI) adalah...",
            "options" => [
                "a" => "Joule",
                "b" => "Watt",
                "c" => "Newton",
                "d" => "Pascal"
            ],
            "answer" => "c"
        ],
        [
            "id" => "5",
            "type" => "mcq",
            "question" => "Sebuah benda memiliki massa 10 kg dan mengalami percepatan 2 m/s². Berapakah gaya yang bekerja pada benda tersebut?",
            "options" => [
                "a" => "5 N",
                "b" => "12 N",
                "c" => "20 N",
                "d" => "100 N"
            ],
            "answer" => "c"
        ],
        [
            "id" => "6",
            "type" => "mcq",
            "question" => "Apa yang dimaksud dengan inersia?",
            "options" => [
                "a" => "Gaya yang menyebabkan benda bergerak",
                "b" => "Kecenderungan benda untuk mempertahankan keadaannya",
                "c" => "Percepatan benda karena gravitasi",
                "d" => "Gaya gesek antara dua permukaan"
            ],
            "answer" => "b"
        ],
        [
            "id" => "7",
            "type" => "mcq",
            "question" => "Ketika Anda mengerem mendadak di dalam mobil, tubuh Anda terdorong ke depan. Fenomena ini terkait dengan Hukum Newton ke-",
            "options" => [
                "a" => "I",
                "b" => "II",
                "c" => "III",
                "d" => "IV"
            ],
            "answer" => "a"
        ],
        [
            "id" => "8",
            "type" => "mcq",
            "question" => "Gaya normal adalah gaya yang...",
            "options" => [
                "a" => "Sejajar dengan permukaan kontak",
                "b" => "Tegak lurus dengan permukaan kontak",
                "c" => "Menarik benda ke pusat bumi",
                "d" => "Menghambat gerakan benda"
            ],
            "answer" => "b"
        ],
        [
            "id" => "9",
            "type" => "mcq",
            "question" => "Jika resultan gaya yang bekerja pada suatu benda adalah nol, maka benda tersebut akan...",
            "options" => [
                "a" => "Bergerak dengan percepatan konstan",
                "b" => "Bergerak dengan kecepatan konstan atau diam",
                "c" => "Berhenti seketika",
                "d" => "Mengalami perubahan arah gerak"
            ],
            "answer" => "b"
        ],
        [
            "id" => "10",
            "type" => "mcq",
            "question" => "Prinsip kerja roket didasarkan pada Hukum Newton ke-",
            "options" => [
                "a" => "I",
                "b" => "II",
                "c" => "III",
                "d" => "IV"
            ],
            "answer" => "c"
        ],
        [
            "id" => "11",
            "type" => "essay",
            "question" => "Jelaskan konsep inersia dan berikan contohnya dalam kehidupan sehari-hari."
        ],
        [
            "id" => "12",
            "type" => "essay",
            "question" => "Bagaimana Hukum Newton III dapat menjelaskan prinsip kerja roket?"
        ]
    ];
    file_put_contents($questionsDataFile, json_encode($sampleQuestions, JSON_PRETTY_PRINT));
}
if (!file_exists($answersDataFile)) {
    file_put_contents($answersDataFile, json_encode([], JSON_PRETTY_PRINT));
}

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true); // For JSON requests

// Check if it's a file upload (LKPD) or a JSON request
$action = isset($_POST['action']) ? $_POST['action'] : (isset($data['action']) ? $data['action'] : '');

switch ($action) {
    case 'login':
        handleLogin($data, $userDataFile);
        break;
    case 'get_questions':
        handleGetQuestions($questionsDataFile);
        break;
    case 'submit_answers':
        handleSubmitAnswers($data, $answersDataFile);
        break;
    case 'submit_lkpd':
        handleSubmitLKPD($_POST, $_FILES, $lkpdUploadDir);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenal.']);
        break;
}

function handleLogin($data, $userDataFile) {
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    $users = json_decode(file_get_contents($userDataFile), true);

    $authenticated = false;
    foreach ($users as $user) {
        if ($user['username'] === $username && $user['password'] === $password) {
            $authenticated = true;
            break;
        }
    }

    if ($authenticated) {
        echo json_encode(['success' => true, 'message' => 'Login berhasil!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Username atau password salah.']);
    }
}

function handleGetQuestions($questionsDataFile) {
    $questions = json_decode(file_get_contents($questionsDataFile), true);
    echo json_encode(['success' => true, 'data' => $questions]);
}

function handleSubmitAnswers($data, $answersDataFile) {
    $namaLengkap = $data['namaLengkap'] ?? '';
    $kelas = $data['kelas'] ?? '';
    $answers = $data['answers'] ?? [];
    $timestamp = $data['timestamp'] ?? date('Y-m-d H:i:s');

    if (empty($namaLengkap) || empty($kelas) || empty($answers)) {
        echo json_encode(['success' => false, 'message' => 'Nama lengkap, kelas, dan jawaban tidak boleh kosong.']);
        return;
    }

    $currentAnswers = json_decode(file_get_contents($answersDataFile), true);
    $currentAnswers[] = [
        'namaLengkap' => $namaLengkap,
        'kelas' => $kelas,
        'answers' => $answers,
        'timestamp' => $timestamp
    ];

    if (file_put_contents($answersDataFile, json_encode($currentAnswers, JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true, 'message' => 'Jawaban berhasil disimpan.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan jawaban.']);
    }
}

function handleSubmitLKPD($postData, $fileData, $uploadDir) {
    $kelompok = $postData['kelompok'] ?? '';
    $namaSiswa1 = $postData['namaSiswa1'] ?? '';
    $namaSiswa2 = $postData['namaSiswa2'] ?? '';
    $namaSiswa3 = $postData['namaSiswa3'] ?? '';

    if (empty($kelompok) || empty($namaSiswa1) || empty($namaSiswa2) || empty($namaSiswa3)) {
        echo json_encode(['success' => false, 'message' => 'Semua kolom nama siswa dan kelompok harus diisi.']);
        return;
    }

    if (!isset($fileData['fileLKPD']) || $fileData['fileLKPD']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Gagal mengunggah file LKPD.']);
        return;
    }

    $file = $fileData['fileLKPD'];
    $fileName = basename($file['name']);
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    $allowedExtensions = ['pdf'];

    if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
        echo json_encode(['success' => false, 'message' => 'Hanya file PDF yang diizinkan.']);
        return;
    }

    // Generate a unique file name to prevent overwrites
    $uniqueFileName = uniqid('lkpd_') . '.' . $fileExtension;
    $targetFilePath = $uploadDir . $uniqueFileName;

    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        // Optionally, save LKPD submission details to a JSON file or database
        $lkpdSubmissionsFile = 'data/lkpd_submissions.json';
        if (!file_exists($lkpdSubmissionsFile)) {
            file_put_contents($lkpdSubmissionsFile, json_encode([], JSON_PRETTY_PRINT));
        }
        $currentSubmissions = json_decode(file_get_contents($lkpdSubmissionsFile), true);
        $currentSubmissions[] = [
            'kelompok' => $kelompok,
            'namaSiswa' => [$namaSiswa1, $namaSiswa2, $namaSiswa3],
            'fileName' => $uniqueFileName,
            'filePath' => $targetFilePath,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        file_put_contents($lkpdSubmissionsFile, json_encode($currentSubmissions, JSON_PRETTY_PRINT));

        echo json_encode(['success' => true, 'message' => 'File LKPD berhasil diunggah.', 'filePath' => $targetFilePath]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memindahkan file yang diunggah.']);
    }
}
?>
