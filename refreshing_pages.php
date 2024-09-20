<!-- refreshing_pages.php -->
<?php
    // Set the refresh interval (in seconds)
    $refreshInterval = 5; // 60 seconds = 1 minute
?>

<!-- Auto-refresh using JavaScript -->
<script>
    setTimeout(function(){
        window.location.reload(1);
    }, <?php echo $refreshInterval * 1000; ?>); // Convert seconds to milliseconds
</script>
