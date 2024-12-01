<?php
require_once '../login/dbh.inc.php';

?>
<div class="card mb-3">
    <div class="profile-container d-flex px-3 pt-3">
        <a href="profile-landing.php?id=<?php echo htmlspecialchars($row['admin_id']); ?>">
            <img src="<?php echo '../admin/uploads/' . htmlspecialchars($row['profile_picture']); ?>" alt="Profile Picture" style="width: 30px;height: 30px;object-fit: cover;border-radius: 50%">
        </a>
        <a href=" profile-landing.php?id=<?php echo htmlspecialchars($row['admin_id']); ?>">
            <p class="ms-1 mt-1" style="display: inline-block; text-decoration:none; color: black;"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></p>
        </a>
    </div>

    <?php if (!empty($row['image'])): ?>
        <div class="image-container mx-3" style="position: relative; overflow: hidden;">
            <div class="blur-background"></div>
            <a href="../admin/uploads/<?php echo htmlspecialchars($row['image']); ?>" data-lightbox="image-<?php echo $row['announcement_id']; ?>" data-title="<?php echo htmlspecialchars($row['title']); ?>">
                <img src="../admin/uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Post Image" class="img-fluid">
                <script src="../admin/js/blur.js"></script>
            </a>
        </div>
    <?php endif; ?>

    <div class="card-body">
        <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
        <div class="card-text">
            <p class="mb-2"><?php echo htmlspecialchars($row['description']); ?></p>

            Tags:
            <?php
            $all_tags = array_merge(explode(',', $row['year_levels']), explode(',', $row['departments']), explode(',', $row['courses']));
            foreach ($all_tags as $tag) : ?>
                <span class="badge rounded-pill bg-danger mb-2"><?php echo htmlspecialchars(trim($tag)); ?></span>
            <?php endforeach; ?>
        </div>

        <small>Updated at <?php echo htmlspecialchars(date('F d, Y', strtotime($row['updated_at']))); ?></small>
    </div>
</div>