<h3>Payment</h3>
<section>
    <div class="row">
        <h3> Payment Information</h3>
        <div class="row">
            <div class="col-md-4 my-2">
                <label class="form-control-label">Journal Preferred Delivery: </label>
                <select name="journal_preferred_delivery" class="form-control form-select select2"
                    id="journal_preferred_delivery">
                    <option value="email" selected>Email</option>
                    <option value="post">Post</option>
                </select>
            </div>
            <input type="hidden" value="{{ 20 }}" name="preferred_delivery_price"
                id="preferred_delivery_price">
            <div class="col-md-8 d-flex">
                <div class="col-md-3 my-2">
                    <label class="form-control-label">Membership Fee : </label>
                    <h4 id="membership_fee_block">${{ number_format(20, 2) }} (Email)</h4>
                </div>
                <div class="col-md-3 my-2">
                    <label class="form-control-label">New Member : </label>
                    <h4 id="new_member_block">$20.00</h4>
                </div>
                <div class="col-md-3 my-2">
                    <label class="form-control-label">Total : </label>
                    <h4 id="total_block">${{ number_format(20, 2) }}</h4>
                </div>
            </div>
        </div>
        <input type="hidden" id="stripe_key" value="{{ env('STRIPE_KEY') }}">
        <div class="row">
            <div class="col-12">
                <div id="card-element"></div>
            </div>
            <div class="col-12">
                <div id="address-element"></div>
            </div>
            <div class="col-12">
                <div id="error-message"></div>
            </div>
        </div>
    </div>
</section>

<script>
    window.addEventListener('DOMContentLoaded', function() {
        jQuery(document).on("change", "#journal_preferred_delivery", function(e) {
            const delivery = jQuery(e.target).val();
            const membership_fee_block = $("#membership_fee_block");
            const new_member_block = $("#new_member_block");
            const total_block = $("#total_block");

            if (delivery == "post") {
                $(membership_fee_block).text(
                    "${{ number_format(20, 2) }} (Post)"
                );
                $(total_block).text("${{ number_format(20, 2) }}");
                $("#preferred_delivery_price").val(
                    "{{ 20 }}");
            } else {
                $(membership_fee_block).text(
                    "{{ number_format(20, 2) }} (Email)"
                );
                $(total_block).text("${{ number_format(20, 2) }}");
                $("#preferred_delivery_price").val(
                    "{{ 20 }}");
            }
        })
    });
</script>
