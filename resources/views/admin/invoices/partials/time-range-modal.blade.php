<!-- Time Range Selection Modal -->
<div class="modal fade" id="timeRangeModal" tabindex="-1" role="dialog" aria-labelledby="timeRangeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="timeRangeModalLabel">
                    <i class="fa fa-clock-o"></i> Select Time Range
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="timeRangeForm" data-download-url="{{ route('admin.invoices.download-custom-csv') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="date">Select Date:</label>
                        <input type="date" 
                               class="form-control" 
                               id="date" 
                               name="date" 
                               value="{{ date('Y-m-d') }}" 
                               data-default-date="{{ date('Y-m-d') }}"
                               required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="start_time">Start Time:</label>
                            <input type="time" 
                                   class="form-control" 
                                   id="start_time" 
                                   name="start_time" 
                                   value="00:00" 
                                   required>
                            <small class="text-muted">Default: 12:00 AM</small>
                        </div>
                        
                        <div class="form-group col-md-6">
                            <label for="end_time">End Time:</label>
                            <input type="time" 
                                   class="form-control" 
                                   id="end_time" 
                                   name="end_time" 
                                   value="23:59" 
                                   required>
                            <small class="text-muted">Default: 11:59 PM</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Quick Presets:</label>
                        <div class="btn-group btn-group-sm d-flex">
                            <button type="button" class="btn btn-outline-primary preset-btn flex-fill" data-start="00:00" data-end="15:00">
                                <i class="fa fa-sun-o"></i> Morning (12AM-3PM)
                            </button>
                            <button type="button" class="btn btn-outline-primary preset-btn flex-fill" data-start="15:00" data-end="23:59">
                                <i class="fa fa-moon-o"></i> Evening (3PM-12AM)
                            </button>
                            <button type="button" class="btn btn-outline-primary preset-btn flex-fill" data-start="00:00" data-end="23:59">
                                <i class="fa fa-calendar"></i> Full Day
                            </button>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fa fa-info-circle"></i>
                        <span id="timeRangePreview">Selected: {{ date('Y-m-d') }} from 00:00 to 23:59</span>
                    </div>
                    
                    <!-- Progress Bar for Download -->
                    <div class="progress mt-3" id="downloadProgress" style="display: none;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                             role="progressbar" 
                             style="width: 0%" 
                             id="downloadProgressBar">
                            Processing...
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" id="downloadBtn" class="btn btn-primary">
                        <i class="fa fa-download"></i> Download CSV
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>