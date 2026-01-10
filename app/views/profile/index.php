<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4 fade-in-up">
                <h1 class="h3 mb-0 text-gradient">My Profile</h1>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 fade-in-up" style="animation-delay: 0.1s;">
            <div class="neu-card mb-4">
                <form action="<?php echo APP_URL; ?>/profile/update" method="post" enctype="multipart/form-data">
                    <div class="text-center mb-4">
                        <div class="d-inline-block rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 position-relative"
                            style="width: 120px; height: 120px; box-shadow: var(--neumorphic-flat); padding: 5px; background: var(--bg-color);">
                            <?php if (!empty($data['user']->profile_pic)): ?>
                                <img src="<?php echo APP_URL . '/' . $data['user']->profile_pic; ?>" alt="Profile"
                                    class="rounded-circle w-100 h-100 object-fit-cover">
                            <?php else: ?>
                                <i class="fas fa-user" style="font-size: 3rem; color: var(--primary);"></i>
                            <?php endif; ?>

                            <label for="profile_upload"
                                class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2 shadow-sm"
                                style="cursor: pointer; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-camera small"></i>
                            </label>
                            <input type="file" name="profile_pic" id="profile_upload" class="d-none" accept="image/*"
                                onchange="previewImage(this)">
                        </div>
                        <h4 class="text-secondary"><?php echo $data['user']->name; ?></h4>
                        <span class="badge rounded-pill bg-light text-secondary shadow-sm">User ID:
                            <?php echo $data['user']->id; ?></span>
                    </div>

                    <script>
                        function previewImage(input) {
                            if (input.files && input.files[0]) {
                                var reader = new FileReader();
                                reader.onload = function (e) {
                                    let container = input.parentElement;
                                    let img = container.querySelector('img');
                                    if (!img) {
                                        // If no image exists, replace icon with img
                                        let icon = container.querySelector('i.fa-user');
                                        if (icon) icon.remove();
                                        img = document.createElement('img');
                                        img.className = 'rounded-circle w-100 h-100 object-fit-cover';
                                        container.prepend(img);
                                    }
                                    img.src = e.target.result;
                                }
                                reader.readAsDataURL(input.files[0]);
                            }
                        }
                    </script>
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label ms-1 text-secondary fw-bold small">Full Name</label>
                            <input type="text" name="name"
                                class="form-control-neu w-100 <?php echo (!empty($data['name_err'])) ? 'is-invalid' : ''; ?>"
                                value="<?php echo $data['name']; ?>">
                            <span class="invalid-feedback d-block">
                                <?php echo $data['name_err']; ?>
                            </span>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label ms-1 text-secondary fw-bold small">Email
                                Address</label>
                            <input type="email" class="form-control-neu w-100 text-muted"
                                value="<?php echo $data['email']; ?>" readonly
                                style="box-shadow: var(--neumorphic-pressed); background-color: rgba(0,0,0,0.02);">
                            <small class="text-muted ms-1">Email cannot be changed directly.</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label ms-1 text-secondary fw-bold small">Phone Number</label>
                            <input type="text" name="phone" class="form-control-neu w-100"
                                value="<?php echo $data['phone']; ?>">
                        </div>

                        <div class="col-12 mt-4 mb-2">
                            <div class="border-bottom border-light"></div>
                            <h5 class="text-primary mt-3 mb-3"><i class="fas fa-lock me-2"></i> Change Password</h5>
                            <small class="text-muted d-block mb-3">Leave blank if you don't want to change the
                                password.</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label ms-1 text-secondary fw-bold small">New
                                Password</label>
                            <input type="password" name="password"
                                class="form-control-neu w-100 <?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>">
                            <span class="invalid-feedback d-block">
                                <?php echo $data['password_err']; ?>
                            </span>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label ms-1 text-secondary fw-bold small">Confirm
                                Password</label>
                            <input type="password" name="confirm_password"
                                class="form-control-neu w-100 <?php echo (!empty($data['confirm_password_err'])) ? 'is-invalid' : ''; ?>">
                            <span class="invalid-feedback d-block">
                                <?php echo $data['confirm_password_err']; ?>
                            </span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn-neu btn-neu-primary">
                            <i class="fas fa-save me-2"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>