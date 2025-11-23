<!-- tags: linux, users, permissions, lpic1 -->

![Linux admin banner](https://sysadmins.tech/wp-content/uploads/2024/10/cropped-it_code-e1729250166879.png)

# Linux user management basics

Managing users and permissions is one of the core tasks of a Linux administrator.  
In this note we’ll quickly cover:

- how to create and remove users  
- how to manage groups  
- how to inspect permissions  
- how to switch users safely  

> Info: These commands assume you have `sudo` access.  
> Always be careful when removing users or modifying home directories.

---

## 1. Inspecting the current user and identity

Start with who you are and which groups you belong to:

```bash
whoami
id
groups
```

###Example:

```bash
$ whoami
mile

$ id
uid=1000(mile) gid=1000(mile) groups=1000(mile),27(sudo)

$ groups
mile : mile sudo
```

Useful to confirm whether you have `sudo` or other special privileges.

## 2. Creating a new user

The recommended way on most modern distros is `useradd` (sometimes wrapped by `adduser`).

### Create a user with a home directory

```bash
sudo useradd -m -s /bin/bash alice
sudo passwd alice
```


Check the entry in `/etc/passwd`:

```bash
grep '^alice:' /etc/passwd
```
Example output:
```bash
alice:x:1001:1001::/home/alice:/bin/bash
```


## 3. Adding the user to groups
Let’s say you want `alice` to have `sudo` access:
```bash
sudo usermod -aG sudo alice  # -aG means “append to these groups.”
```

Verify:
```bash
id alice
groups alice
```

You should see `sudo` in the group list.

Note: Group membership typically applies at login. Ask the user to log out and log back in.


