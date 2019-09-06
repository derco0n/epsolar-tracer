#include <linux/build-salt.h>
#include <linux/module.h>
#include <linux/vermagic.h>
#include <linux/compiler.h>

BUILD_SALT;

MODULE_INFO(vermagic, VERMAGIC_STRING);
MODULE_INFO(name, KBUILD_MODNAME);

__visible struct module __this_module
__attribute__((section(".gnu.linkonce.this_module"))) = {
	.name = KBUILD_MODNAME,
	.init = init_module,
#ifdef CONFIG_MODULE_UNLOAD
	.exit = cleanup_module,
#endif
	.arch = MODULE_ARCH_INIT,
};

#ifdef CONFIG_RETPOLINE
MODULE_INFO(retpoline, "Y");
#endif

static const struct modversion_info ____versions[]
__used
__attribute__((section("__versions"))) = {
	{ 0xae9bea50, "module_layout" },
	{ 0x7a4944fb, "usb_deregister" },
	{ 0x7c32d0f0, "printk" },
	{ 0x22befa1a, "put_tty_driver" },
	{ 0x43612f6f, "tty_unregister_driver" },
	{ 0xd6804dcb, "usb_register_driver" },
	{ 0x1bc45da0, "tty_register_driver" },
	{ 0xe2c0f7ec, "tty_set_operations" },
	{ 0x67b27ec1, "tty_std_termios" },
	{ 0xe18b5de2, "__tty_alloc_driver" },
	{ 0xf9760aed, "tty_port_register_device" },
	{ 0x1ccb136, "usb_get_intf" },
	{ 0xbb6660cd, "usb_driver_claim_interface" },
	{ 0x4defcf39, "_dev_info" },
	{ 0x12da5bb2, "__kmalloc" },
	{ 0xdba8efda, "device_create_file" },
	{ 0x82dd0749, "_dev_warn" },
	{ 0x5a095d9f, "usb_alloc_urb" },
	{ 0xff1537d7, "usb_alloc_coherent" },
	{ 0x98ed13b2, "tty_port_init" },
	{ 0xe346f67a, "__mutex_init" },
	{ 0xf0dee418, "usb_ifnum_to_if" },
	{ 0xf4fa543b, "arm_copy_to_user" },
	{ 0x5f754e5a, "memset" },
	{ 0xbc10dd97, "__put_user_4" },
	{ 0x4ae52822, "kmem_cache_alloc_trace" },
	{ 0x6caec441, "kmalloc_caches" },
	{ 0xc6cbbc89, "capable" },
	{ 0x28cc25db, "arm_copy_from_user" },
	{ 0x353e3fa5, "__get_user_4" },
	{ 0x71c90087, "memcmp" },
	{ 0x409873e3, "tty_termios_baud_rate" },
	{ 0xe707d823, "__aeabi_uidiv" },
	{ 0x62e7f6a6, "usb_autopm_put_interface" },
	{ 0xe8cd469, "usb_autopm_get_interface" },
	{ 0xdb7305a1, "__stack_chk_fail" },
	{ 0x8f678b07, "__stack_chk_guard" },
	{ 0xb78427cd, "tty_standard_install" },
	{ 0xce90062e, "refcount_inc_not_zero_checked" },
	{ 0x1af645cf, "tty_port_open" },
	{ 0x3afe764c, "tty_port_close" },
	{ 0xe1716b6d, "usb_autopm_get_interface_async" },
	{ 0xdad6ef5e, "tty_port_hangup" },
	{ 0xc18e0641, "tty_port_tty_wakeup" },
	{ 0x37a0cba, "kfree" },
	{ 0x73ecca9c, "usb_put_intf" },
	{ 0x13d4fc6a, "tty_flip_buffer_push" },
	{ 0xa04bffd9, "tty_insert_flip_string_fixed_flag" },
	{ 0xb2d48a2e, "queue_work_on" },
	{ 0x2d3385d3, "system_wq" },
	{ 0x91715312, "sprintf" },
	{ 0x3ea9d8ba, "tty_port_put" },
	{ 0x5f35aebd, "usb_driver_release_interface" },
	{ 0x638d83a1, "usb_free_urb" },
	{ 0x49dd5189, "tty_unregister_device" },
	{ 0x26227edf, "tty_kref_put" },
	{ 0x7b38af58, "tty_vhangup" },
	{ 0xf8e014ac, "tty_port_tty_get" },
	{ 0x67ea780, "mutex_unlock" },
	{ 0xdd4de1a9, "device_remove_file" },
	{ 0xc271c3be, "mutex_lock" },
	{ 0x4f5b36d1, "usb_free_coherent" },
	{ 0xdb9ca3c5, "_raw_spin_lock" },
	{ 0x4205ad24, "cancel_work_sync" },
	{ 0x1eed64a6, "usb_kill_urb" },
	{ 0x676bbc0f, "_set_bit" },
	{ 0x2a3aa678, "_test_and_clear_bit" },
	{ 0xd697e69a, "trace_hardirqs_on" },
	{ 0x2da81bff, "_raw_spin_lock_irq" },
	{ 0x1724f92e, "usb_autopm_put_interface_async" },
	{ 0x96a4691c, "tty_port_tty_hangup" },
	{ 0x2e9485dd, "_dev_err" },
	{ 0xed492be8, "usb_submit_urb" },
	{ 0x526c3a6c, "jiffies" },
	{ 0x9d669763, "memcpy" },
	{ 0xc8bfea43, "usb_control_msg" },
	{ 0x2e5810c6, "__aeabi_unwind_cpp_pr1" },
	{ 0x39a12ca7, "_raw_spin_unlock_irqrestore" },
	{ 0x5f849a69, "_raw_spin_lock_irqsave" },
	{ 0xb1ad28e0, "__gnu_mcount_nc" },
};

static const char __module_depends[]
__used
__attribute__((section(".modinfo"))) =
"depends=";

MODULE_ALIAS("usb:v04E2p1410d*dc*dsc*dp*ic*isc*ip*in*");
MODULE_ALIAS("usb:v04E2p1411d*dc*dsc*dp*ic*isc*ip*in*");
MODULE_ALIAS("usb:v04E2p1412d*dc*dsc*dp*ic*isc*ip*in*");
MODULE_ALIAS("usb:v04E2p1414d*dc*dsc*dp*ic*isc*ip*in*");
MODULE_ALIAS("usb:v04E2p1420d*dc*dsc*dp*ic*isc*ip*in*");
MODULE_ALIAS("usb:v04E2p1421d*dc*dsc*dp*ic*isc*ip*in*");
MODULE_ALIAS("usb:v04E2p1422d*dc*dsc*dp*ic*isc*ip*in*");
MODULE_ALIAS("usb:v04E2p1424d*dc*dsc*dp*ic*isc*ip*in*");
MODULE_ALIAS("usb:v04E2p1400d*dc*dsc*dp*ic*isc*ip*in*");
MODULE_ALIAS("usb:v04E2p1401d*dc*dsc*dp*ic*isc*ip*in*");
MODULE_ALIAS("usb:v04E2p1402d*dc*dsc*dp*ic*isc*ip*in*");
MODULE_ALIAS("usb:v04E2p1403d*dc*dsc*dp*ic*isc*ip*in*");

MODULE_INFO(srcversion, "DBED417C4D33EA9F4345F0E");
