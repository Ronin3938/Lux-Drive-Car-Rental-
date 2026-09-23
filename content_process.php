<?php
session_start();
require_once 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Terms & Conditions
    if (isset($_POST['delete_terms'])) {
        $deleteId = $_POST['delete_terms_id'];
        $stmt = $pdo->prepare("DELETE FROM terms_and_conditions WHERE id = :id");
        $stmt->execute([':id' => $deleteId]);
    }

    // FAQ
    if (isset($_POST['delete_faq'])) {
        $deleteId = $_POST['delete_faq_id'];
        $stmt = $pdo->prepare("DELETE FROM faq WHERE id = :id");
        $stmt->execute([':id' => $deleteId]);
    }
    if (isset($_POST['add_faq'])) {
        $question = $_POST['question'];
        $answer = $_POST['answer'];
        $stmt = $pdo->prepare("INSERT INTO faq (question, answer, created_at) VALUES (:question, :answer, NOW())");
        $stmt->execute([':question' => $question, ':answer' => $answer]);
    }

    // Contact
    if (isset($_POST['delete_contact'])) {
        $deleteId = $_POST['delete_contact_id'];
        $stmt = $pdo->prepare("DELETE FROM contact_details WHERE id = :id");
        $stmt->execute([':id' => $deleteId]);
    }
    if (isset($_POST['add_contact'])) {
        $type    = $_POST['contact_type'];
        $email   = $_POST['email'];
        $phone   = $_POST['phone_number'];
        $address = $_POST['address'];

        $stmt = $pdo->prepare("
            INSERT INTO contact_details 
            (contact_type, email, phone_number, address, status, created_at, updated_at) 
            VALUES (:type, :email, :phone, :address, 'active', NOW(), NOW())
        ");
        $stmt->execute([
            ':type'   => $type,
            ':email'  => $email,
            ':phone'  => $phone,
            ':address' => $address
        ]);
    }

    header("Location: admin_dashboard.php");
    exit;
}
