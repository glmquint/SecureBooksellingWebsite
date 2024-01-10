<?php if (isset($_SESSION['success'])): ?>
    <div class="notice success" style="background-color: lightgreen; color: black">
        <p>
            <?php
            echo htmlspecialchars($_SESSION['success']);
            unset($_SESSION['success']);
            ?>
        </p>
    </div>
<?php elseif (isset($_SESSION['errorMsg'])): ?>

    <div class="notice error warning" style="background-color: lightcoral; color: black">
        <p>
            <?php
            echo htmlspecialchars($_SESSION['errorMsg']);
            unset($_SESSION['errorMsg']);
            ?>
        </p>
    </div>
<?php endif ?>