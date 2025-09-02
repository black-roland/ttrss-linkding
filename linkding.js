Plugins.Linkding = {

	shareArticleToLinkding: function (id) {
		try {
			Notify.progress("Adding to Linkding", true);
			xhr.post("backend.php",
				{
					op: "PluginHandler",
					plugin: "linkding",
					method: "getInfo",
					id: encodeURIComponent(id)
				},
				(reply) => {
					var ti = JSON.parse(reply);
					if (ti.status == 200 || ti.status == 201) {
						Notify.info("Added to Linkding:<br/><em>" + ti.title + "</em>");
					} else {
						Notify.error("<strong>Error adding to Linkding:</strong><br/>" + ti.message || "unknown");
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
			App.hotkey_actions['linkding_save'] = function () {
				if (Article.getActive()) {
					var artid = "ld" + Article.getActive();
					Plugins.Linkding.shareArticleToLinkding(Article.getActive(), document.getElementById(artid));
					return;
				}
			};
		});
	});
});
