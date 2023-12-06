<div class="tab-pane fade <?php showClass($contentPayment->id, 1, 'show active') ?>" id="kt_tab_pane_<?php echo $contentPayment->tag ?>" role="tabpanel">
    <form>
        <label class="form-check form-switch form-check-custom form-check-solid">
            <input class="form-check-input" type="checkbox" value="" <?php showArgument(1, $contentPayment->status, 'checked="checked"')?>>
            <span class="form-check-label fw-bold text-muted"></span>
        </label>
    </form>
</div>