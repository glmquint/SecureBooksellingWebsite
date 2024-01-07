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

    <div style="position: absolute; bottom: 0; right: 0;" class="error warning">
        <h3>
            <?php
            echo htmlspecialchars($_SESSION['errorMsg']);
            unset($_SESSION['errorMsg']);
            ?>
        </h3>
    </div>
<?php endif ?>