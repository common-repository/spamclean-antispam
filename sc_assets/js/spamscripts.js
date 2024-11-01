jQuery(document).ready(function () {
    jQuery(".scdashboard").show();
    jQuery(".scfilter").hide();
    jQuery(".scadvanced").hide();
    jQuery(".scmore").hide();
    jQuery(".scantibtn").hide();

    jQuery(".scfilterlabel").click(function () {
        jQuery(".scdashboard").hide();
        jQuery(".scfilter").show();
        jQuery(".scadvanced").hide();
        jQuery(".scmore").hide();
        jQuery(".scantibtn").show();
    });
    jQuery(".scadvancedlabel").click(function () {
        jQuery(".scdashboard").hide();
        jQuery(".scfilter").hide();
        jQuery(".scadvanced").show();
        jQuery(".scmore").hide();
        jQuery(".scantibtn").show();
    });
    jQuery(".scblocklabel").click(function () {
        jQuery(".scdashboard").hide();
        jQuery(".scfilter").hide();
        jQuery(".scadvanced").hide();
        jQuery(".scmore").show();
        jQuery(".scantibtn").show();
    });
    jQuery(".scdashboardlabel").click(function () {
        jQuery(".scfilter").hide();
        jQuery(".scadvanced").hide();
        jQuery(".scmore").hide();
        jQuery(".scdashboard").show();
        jQuery(".scantibtn").hide();
    });
    jQuery(".scdashboardforspam").addClass('scactive');
    jQuery(".scdashboardlabel").click(function () {
        jQuery(".scdashboardforspam").addClass('scactive');
        jQuery(".scaddforspam").removeClass('scactive');
        jQuery(".scadvancedforspam").removeClass('scactive');
        jQuery(".scblockforspam").removeClass('scactive');
    });

    jQuery(".scfilterlabel").click(function () {
        jQuery(".scdashboardforspam").removeClass('scactive');
        jQuery(".scaddforspam").addClass('scactive');
        jQuery(".scadvancedforspam").removeClass('scactive');
        jQuery(".scblockforspam").removeClass('scactive');
    });
    jQuery(".scadvancedlabel").click(function () {
        jQuery(".scdashboardforspam").removeClass('scactive');
        jQuery(".scaddforspam").removeClass('scactive');
        jQuery(".scadvancedforspam").addClass('scactive');
        jQuery(".scblockforspam").removeClass('scactive');
    });
    jQuery(".scblocklabel").click(function () {
        jQuery(".scdashboardforspam").removeClass('scactive');
        jQuery(".scaddforspam").removeClass('scactive');
        jQuery(".scadvancedforspam").removeClass('scactive');
        jQuery(".scblockforspam").addClass('scactive');
    });
    jQuery(".scspamnav").height(jQuery("#section0").height());
    jQuery(".scdashboardforspam").click(function () {
        jQuery(".scspamnav").height(jQuery("#section0").height());
    });
    jQuery(".scaddforspam").click(function () {
        jQuery(".scspamnav").height(jQuery("#section1").height());
    });
    jQuery(".scadvancedforspam").click(function () {
        jQuery(".scspamnav").css('height', '331px');
    });
    jQuery(".scblockforspam").click(function () {
        jQuery(".scspamnav").css('height', '395px');
    });
    jQuery(".spam_clean_overlay").css('min-height', '1002px');
    jQuery(".scoverlaydash").click(function () {
        jQuery(".spam_clean_overlay").css('min-height', '1002px');
    });
    jQuery(".scoverlayfil").click(function () {
        jQuery(".spam_clean_overlay").css('min-height', '493px');
    });
    jQuery(".scoverlayadv").click(function () {
        jQuery(".spam_clean_overlay").css('min-height', '441px');
    });
});