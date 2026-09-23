<?php
session_start();
require_once 'dbconnect.php';

try {
    // Get all active FAQ questions, ordered by category and then by ID
    $query = "SELECT * FROM faq WHERE status = 'active' ORDER BY category, id";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group FAQs by category for easier display
    $groupedFaqs = [];
    foreach ($faqs as $faq) {
        $groupedFaqs[$faq['category']][] = $faq;
    }
} catch (PDOException $e) {
    // In a production environment, you would log this error and show a user-friendly message
    $groupedFaqs = []; // Clear the array to prevent display of partial data
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Lux Drive</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .faq-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .faq-container h1 {
            text-align: center;
            color: #1a8d55ff;
            margin-bottom: 30px;
        }

        .category-section {
            margin-bottom: 25px;
        }

        .category-section h2 {
            background-color: #034d2eff;
            color: #fafafcff;
            padding: 5px;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: center;
        }

        .faq-item {
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            transition: box-shadow 0.3s ease;
        }

        .faq-item:hover {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .faq-question {
            background-color: #fff;
            color: #a3b5c3ff;
            padding: 15px 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
        }

        .faq-question i {
            transition: transform 0.3s ease;
        }

        .faq-question.active i {
            transform: rotate(180deg);
        }

        .faq-answer {
            background-color: #f9f9f9;
            color: #555;
            padding: 0 20px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-in-out, padding 0.3s ease-in-out;
        }

        .faq-answer.open {
            max-height: 200px;
            padding: 15px 20px;
        }

        .no-faqs {
            text-align: center;
            font-style: italic;
            color: #888;
            margin-top: 50px;
        }

        @media (max-width: 600px) {
            .faq-container {
                padding: 15px;
            }

            .faq-question,
            .faq-answer {
                padding: 10px 15px;
            }
        }
    </style>
</head>

<body>

    <?php require_once 'header.php'; ?>

    <div class="faq-container">
        <h1>Frequently Asked Questions</h1>

        <?php if (!empty($groupedFaqs)): ?>
            <?php foreach ($groupedFaqs as $category => $faqs): ?>
                <div class="category-section">
                    <h2><?= htmlspecialchars($category); ?></h2>
                    <?php foreach ($faqs as $faq): ?>
                        <div class="faq-item">
                            <div class="faq-question">
                                <span><?= htmlspecialchars($faq['question']); ?></span>
                                <i class="fa-solid fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p><?= htmlspecialchars($faq['answer']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-faqs">No FAQs available at the moment. Please check back later.</p>
        <?php endif; ?>
    </div>

    <?php require_once 'footer.php'; ?>

    <script>
        document.querySelectorAll('.faq-question').forEach(button => {
            button.addEventListener('click', () => {
                const answer = button.nextElementSibling;
                button.classList.toggle('active');
                answer.classList.toggle('open');
            });
        });
    </script>
</body>

</html>