Plugins.Linkding = {

	shareArticleToLinkding: function(id, btn) {
		try {

			var d = new Date();
		    var ts = d.getTime();

			Notify.progress("Saving to Linkding…", true);
			xhr.post("backend.php",
			{
				op: "pluginhandler",
				plugin: "Linkding",
				method: "getInfo",
				id: encodeURIComponent(id)
			},
			(reply) => {
				if (reply.status=="200" || reply.status=="201") {
					Notify.info("Saved to Linkding:<br/><em>" + reply.title + "</em>");
				} else {
					Notify.error("<strong>Error saving to Linkding!</strong><br/>("+reply.status+") "+reply.error);
				}
			}
			);
		} catch (e) {
			App.Error.report(e);
		}
	}
};

require(['dojo/_base/kernel', 'dojo/ready'], function (dojo, ready) {
	ready(function () {
		PluginHost.register(PluginHost.HOOK_INIT_COMPLETE, () => {
			App.hotkey_actions['linkding_save'] = function() {
				if (Article.getActive()) {
					var artid = "ld"+Article.getActive();
					Plugins.Linkding.shareArticleToLinkding(Article.getActive(), document.getElementById(artid));
					return;
				}
			};
		});
	});
});
