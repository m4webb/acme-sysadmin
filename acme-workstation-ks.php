<?php
// Kickstart acme workstation
//
// This bootstraps salt and anaconda. All other system config should be
// managed with salt.
header('Content-Type: text/plain');
?>
#version=RHEL7
# System authorization information
auth --enableshadow --passalgo=sha512

# Use CDROM installation media
cdrom
# Use graphical install
graphical
# Run the Setup Agent on first boot
firstboot --disable
ignoredisk --only-use=sda
# Keyboard layouts
keyboard --vckeymap=us --xlayouts='us'
# System language
lang en_US.UTF-8

# Network information
network  --bootproto=dhcp --device=em1 --ipv6=auto --activate
network  --hostname=<?php echo $_GET["hostname"]?>

# Root password
rootpw --iscrypted $6$46W.i4P7b2vmIZYQ$dOGYo5x.IfJl0q/Oi59mSq9N3WWS7i74S82iTMNX4mVuHK0F7zMM1IJ6Rc44/GTs32TG4nPf6bIfFVKkS0Dns1
# System services
services --enabled="chronyd"
# System timezone
timezone America/Denver --isUtc --ntpservers=0.rhel.pool.ntp.org,1.rhel.pool.ntp.org,2.rhel.pool.ntp.org,3.rhel.pool.ntp.org
user --groups=wheel --name=acme --password=$6$XLYCSqjepkQLThyD$G4lTXQG51ys.8iQLQVX7Ggedglodssg/h84UTxpU.iQUx.U0haVkSWHJyPR62PhzqH5fGc7xZZOVz24JSHpW21 --iscrypted --gecos="Acme Admin"
# X Window System configuration information
xconfig  --startxonboot
# System bootloader configuration
bootloader --append=" crashkernel=auto" --location=mbr --boot-drive=sda
autopart --type=lvm
# Partition clearing information
clearpart --all --initlabel --drives=sda

%packages
@base
@core
@desktop-debugging
@dial-up
@directory-client
@fonts
@gnome-desktop
@guest-agents
@guest-desktop-agents
@input-methods
@internet-browser
@java-platform
@multimedia
@network-file-system-client
@networkmanager-submodules
@print-client
@x11
chrony
kexec-tools

%end

%post
subscription-manager register --auto-attach --username=<?php echo $_GET["rhn_user"]?> --password=<?php echo $_GET["rhn_pass"]?>

yum -y install http://linux.mirrors.es.net/fedora-epel/7/x86_64/e/epel-release-7-5.noarch.rpm
yum-config-manager --enable rhel-7-workstation-optional-rpms
yum -y install salt-minion
curl http://10.18.32.52/bootstrap/minion -o "/etc/salt/minion"
systemctl enable salt-minion
systemctl start salt-minion

%end

%addon com_redhat_kdump --enable --reserve-mb='auto'

%end
