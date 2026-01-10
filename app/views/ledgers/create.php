<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-11 fade-in-up">
        <div class="neu-card">
            <div class="mb-4 text-center border-bottom border-light pb-3">
                <h2 class="text-gradient h3"><i class="fas fa-plus-circle me-2"></i> Create New Ledger Account</h2>
                <p class="text-muted small">Fill in the details below to add a new account ledger.</p>
            </div>

            <form action="<?php echo APP_URL; ?>/ledgers/create" method="post">
                <?php echo csrf_field(); ?>

                <div class="mb-5">
                    <h6 class="text-uppercase text-secondary fw-bold mb-4 border-bottom border-light pb-2"><i
                            class="fas fa-info-circle me-2"></i> Basic Details</h6>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label ms-1 text-secondary fw-bold small">Ledger Name *</label>
                            <input type="text" name="name"
                                class="form-control-neu <?php echo (!empty($data['name_err'])) ? 'is-invalid' : ''; ?>"
                                value="<?php echo $data['name']; ?>" required>
                            <span class="invalid-feedback"><?php echo $data['name_err']; ?></span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label ms-1 text-secondary fw-bold small">Under Group *</label>
                            <select name="group_id"
                                class="form-control-neu <?php echo (!empty($data['group_err'])) ? 'is-invalid' : ''; ?>"
                                required style="appearance: auto;">
                                <option value="">Select Group...</option>
                                <?php foreach ($data['groups'] as $group): ?>
                                    <option value="<?php echo $group->id; ?>" <?php echo ($data['group_id'] == $group->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($group->name); ?> (<?php echo $group->nature; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="invalid-feedback"><?php echo $data['group_err']; ?></span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label ms-1 text-secondary fw-bold small">Type</label>
                            <select name="type" class="form-control-neu" id="ledgerType" style="appearance: auto;">
                                <option value="General">General</option>
                                <option value="Customer">Customer (Debtor)</option>
                                <option value="Supplier">Supplier (Creditor)</option>
                                <option value="Bank">Bank Account</option>
                                <option value="Cash">Cash</option>
                                <option value="Tax">Tax / GST</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label ms-1 text-secondary fw-bold small">Opening Balance</label>
                            <input type="number" step="0.01" name="opening_balance" class="form-control-neu"
                                value="<?php echo $data['opening_balance']; ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label ms-1 text-secondary fw-bold small">Dr / Cr</label>
                            <select name="opening_balance_type" class="form-control-neu" style="appearance: auto;">
                                <option value="Dr">Debit</option>
                                <option value="Cr">Credit</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label ms-1 text-secondary fw-bold small">Ledger Code</label>
                            <input type="text" name="code" class="form-control-neu"
                                value="<?php echo $data['code']; ?>">
                        </div>
                    </div>
                </div>

                <!-- Additional Details Sections -->
                <div id="contactDetails" class="mb-5">
                    <h6 class="text-uppercase text-secondary fw-bold mb-4 border-bottom border-light pb-2"><i
                            class="fas fa-address-card me-2"></i> Mailing & Contact Details</h6>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label ms-1 text-secondary fw-bold small">Contact Person</label>
                            <div class="position-relative">
                                <i
                                    class="fas fa-user position-absolute top-50 translate-middle-y ms-3 text-secondary"></i>
                                <input type="text" name="contact_person" class="form-control-neu ps-5"
                                    value="<?php echo $data['contact_person']; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label ms-1 text-secondary fw-bold small">Email</label>
                            <div class="position-relative">
                                <i
                                    class="fas fa-envelope position-absolute top-50 translate-middle-y ms-3 text-secondary"></i>
                                <input type="email" name="email" class="form-control-neu ps-5"
                                    value="<?php echo $data['email']; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label ms-1 text-secondary fw-bold small">Phone/Mobile</label>
                            <div class="position-relative">
                                <i
                                    class="fas fa-phone position-absolute top-50 translate-middle-y ms-3 text-secondary"></i>
                                <input type="text" name="phone" class="form-control-neu ps-5"
                                    value="<?php echo $data['phone']; ?>">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label ms-1 text-secondary fw-bold small">Address</label>
                            <textarea name="address" class="form-control-neu"
                                rows="2"><?php echo $data['address']; ?></textarea>
                        </div>
                    </div>
                </div>

                <div id="taxDetails" class="mb-4">
                    <h6 class="text-uppercase text-secondary fw-bold mb-4 border-bottom border-light pb-2"><i
                            class="fas fa-file-invoice-dollar me-2"></i> Tax / Statutory Details</h6>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label ms-1 text-secondary fw-bold small">GSTIN / Tax No</label>
                            <input type="text" name="gstin" class="form-control-neu"
                                value="<?php echo $data['gstin']; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label ms-1 text-secondary fw-bold small">PAN Number</label>
                            <input type="text" name="pan_no" class="form-control-neu"
                                value="<?php echo $data['pan_no']; ?>">
                        </div>
                    </div>
                </div>

                <div class="mt-5 d-flex justify-content-end gap-3">
                    <a href="<?php echo APP_URL; ?>/ledgers/index" class="btn-neu" style="color: var(--danger);">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
                    <button type="submit" class="btn-neu btn-neu-primary px-4">
                        <i class="fas fa-save me-2"></i> Save Ledger
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>