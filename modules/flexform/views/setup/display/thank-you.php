<div class="ff-statement-wrapper center ff-thankyou-wrapper">
    <?php echo $this->load->view('partials/cover', ['block' => $block], true); ?>
    <div class="preview-statement-title tw-mb-4">
        <?php echo $this->load->view('partials/title-label', ['block' => $block], true); ?>
        <?php echo $this->load->view('partials/description-label', ['block' => $block], true); ?>
        <?php if ($block['redirect_url']): ?>
            <?php
            $url = $block['redirect_url'];
            //append https if not present
            if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
                $url = "https://" . $url;
            }
            ?>
            <div class="tw-mt-4 ff-thankyou-wrapper-redirect-block">
                <p><i class="fa-solid fa-spinner"></i></p>
                <p><?php echo $block['redirect_message'] ?></p>
            </div>
            <?php if (!$preview): ?>
                <script>
                    setTimeout(function() {
                        window.location.replace('<?php echo $url ?>');
                    }, <?php echo (int)$block['redirect_delay'] * 1000 ?>);
                </script>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ($block['confetti'] == 1): ?>
            <script src="<?php echo module_dir_url('flexform', 'assets/js/confetti.browser.min.js') ?>"></script>
            <script>
                //check if confetti is supported
                if (typeof confetti !== 'undefined') {
                // First burst - bigger explosion from bottom
                confetti({
                    particleCount: 150,
                    spread: 100,
                    origin: {
                        y: 0.3
                    },
                    colors: ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff'],
                    startVelocity: 45,  // Increased for better spread from higher position
                    gravity: 1,
                    scalar: 1.2,
                    ticks: 200
                });

                // Second burst - from both sides
                setTimeout(function() {
                    confetti({
                        particleCount: 100,
                        angle: 60,
                        spread: 55,
                        origin: {
                            x: 0
                        },
                        colors: ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff']
                    });
                    confetti({
                        particleCount: 100,
                        angle: 120,
                        spread: 55,
                        origin: {
                            x: 1
                        },
                        colors: ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff']
                    });
                }, 250);
        }
            </script>
        <?php endif; ?>
    </div>
</div>