<hr />

<footer>
	<!-- Link to trigger modal -->
	<p class="pull-right">
		<a href="#modalHelp" data-toggle="modal" data-target="#modalHelp"><i class="glyphicon glyphicon-question-sign"></i> Need Help</a>?
		Williams College, <?php echo date('Y'); ?>
	</p>

	<!-- Modal -->
	<div id="modalHelp" class="modal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalHelpLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
					<h4 class="modal-title" id="modalHelpLabel">Help FAQ</h4>
				</div>
				<div class="modal-body">
					<ol>
						<li><p>When you use GLOW, you are automatically signed into your Signup Sheets account.</p></li>
						<li><p>Alternatively, you may directly access Signup Sheets (independant of GLOW) by clicking here (link coming soon) and signing in with your Williams username and password.</p></li>
					</ol>

					<p>&nbsp;</p>

					<p><i class="glyphicon glyphicon-question-sign"></i> More questions?</p>
					<?php
						if (isset($managersList)) {
							# show list of managers for this group
							echo "<p>Please contact: " . $managersList . "</p>";
						}
						else {
							# show default suypport address
							echo "<p>Please contact: <a href=\"mailto:itech@" . INSTITUTION_DOMAIN . "?subject=SignupSheets_Help_Request\"><i class=\"glyphicon glyphicon-envelope\"></i> itech@williams.edu</a></p>";
						}
					?>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div> <!-- /Modal -->
</footer>

</div> <!-- /container -->

</body>
</html>