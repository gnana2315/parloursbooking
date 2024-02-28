<!DOCTYPE html>
<html lang="en">
    <head>
        @include('includes.head')
    </head>
    <body>
		<div class="super_container">
			<header class="header">
                @include('includes.header')
            </header>
			<div class="super_container_inner">
                @yield('content')
                
                <footer class="footer container_custom">
                    @include('includes.footer')
                </footer>
            </div>
        </div>        
        <div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" id="booking" aria-labelledby="booking" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Booking Form</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="container">
                            <form>
                                <div class="row">
                                    <div class="col-lg-7 col-md-7">
                                        <div class="row">
                                            <div class="col">
                                                <label for="inputDate">Date <span class="requiredInput">*</span></label>
                                                <input type="date" class="form-control" id="inputDate">
                                            </div>
                                            <div class="col">
                                                <label for="inputTimeSlot">Time <span class="requiredInput">*</span></label>														
                                                <select class="form-control" id="inputTimeSlot" name="inputTimeSlot">
                                                    <option value="09:00 - 10.00">9:00 AM - 10.00 AM</option>
                                                    <option value="10:00 - 11:00">10:00 AM - 11:00 AM</option>
                                                    <option value="11:00 - 12:00">11:00 AM - 12:00 PM</option>
                                                    <option value="14:00 - 15:00">2:00 PM - 3:00 PM</option>
                                                    <option value="15:00 - 16:00">3:00 PM - 4:00 PM</option>
                                                    <!-- Add more options as needed -->
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col">
                                                <label for="inputEmail">Email <span class="requiredInput">*</span></label>
                                                <input type="email" class="form-control" id="inputEmail" placeholder="name@example.com">
                                            </div>
                                            <div class="col">
                                                <label for="inputContactNo">Contact No <span class="requiredInput">*</span></label>
                                                <input type="tel" class="form-control" id="inputContactNo" placeholder="0771 234 567">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="inputName">Name <span class="requiredInput">*</span></label>
                                            <input type="text" class="form-control" id="inputName" placeholder="eg: John">
                                        </div>
                                        <div class="form-group">
                                            <label for="inputAdditional">Addional Notes</label>
                                            <textarea class="form-control" id="inputAdditional" rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-lg-5 col-md-5">
                                        <h5>Services Cart</h5>
                                        Payment method: Pay at venue
                                        <table class="table" id="itemsTable">
                                            <thead>
                                                <th scope="col">Service</th>
                                                <th scope="col">Price</th>
                                            </thead>
                                            <tbody id="modalBody">												
                                            </tbody>
                                        </table>
                                        <h5>Cancellation policy</h5>
                                        Please avoid cancelling within <strong>2 hours</strong> of your appointment time												
                                    </div>
                                </div>
                            </form>
                        </div>									
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary">Book</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        @include('includes.foot')
    </body>
</html>