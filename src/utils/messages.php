<?php if (isset($_SESSION['success'])): ?>
    <div class="success">
        <h3>
            <?php
            echo htmlspecialchars($_SESSION['success']);
            unset($_SESSION['success']);
            ?>
        </h3>
    </div>
<?php elseif (isset($_SESSION['errorMsg'])): ?>
    <div class="error warning">
        <h3>
            <?php
            echo htmlspecialchars($_SESSION['errorMsg']);
            unset($_SESSION['errorMsg']);
            ?>
        </h3>
    </div>
<?php endif ?>