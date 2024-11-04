# Looking Glass Project

This project provides a Looking Glass interface that allows users to test network connectivity to a given IP address or hostname using different network diagnostic tools such as `ping`, `traceroute`, and `mtr`. The interface is designed to be simple and user-friendly.

## Getting Started

### Prerequisites

- PHP 8.0 or higher
- Web server (Apache, Nginx, etc.)
- Composer (for managing dependencies)

### Configuration

- **Replace URLs**: Update the URLs used in the project to match your domain:

  - Replace all instances of `https://example.com` with your website's URL.
  - Replace `https://cdn.example.com/logo.png` with the appropriate path to your logo.

- **Update IP Addresses**: Update the example IP addresses to match your test environment:

  - Replace `192.0.2.1` with an actual IPv4 address you want to test.
  - Replace `2001:db8::1` with an actual IPv6 address you want to test.

- **CSRF Tokens**: Ensure that the CSRF token mechanism is correctly configured to secure the form submissions.

### Running the Application

1. Start your web server.
2. Access the Looking Glass tool from your browser by navigating to the URL where your project is hosted.
3. Use the form to enter a hostname or IP address and select the desired network diagnostic tool (`ping`, `traceroute`, `mtr`, etc.).

## Usage

The Looking Glass interface allows you to perform the following network tests:

- **Ping**: Send ICMP packets to test connectivity to an IPv4 address.
- **Traceroute**: Trace the route packets take to reach an IPv4 address.
- **MTR**: Perform a network diagnostic combining the functionality of both `ping` and `traceroute`.
- **Ping6**: Test connectivity to an IPv6 address.
- **Traceroute6**: Trace the route to an IPv6 address.
- **MTR6**: Perform MTR tests for IPv6 addresses.

## Customization

- **HTML & CSS**: Modify the HTML and CSS in `index.php` and the embedded styles to adjust the look and feel of the interface.
- **Custom Scripts**: If you have custom scripts (like additional headers or footers), include them in the project by modifying the relevant sections in the PHP code.

## Security Considerations

- **CSRF Protection**: The form includes CSRF token validation to protect against cross-site request forgery attacks.
- **Command Injection**: User inputs are sanitized using `escapeshellcmd` to prevent command injection vulnerabilities. Ensure this is maintained if modifying the command execution logic.
- **Terms of Service**: Users must agree to the Terms of Service before executing any network diagnostic commands. Make sure the link to your terms is updated.

## License

This project is licensed under the Apache License 2.0. See the [LICENSE](http://www.apache.org/licenses/LICENSE-2.0) file for more details.

## Contributing

Feel free to contribute by submitting pull requests or creating issues. Make sure to follow the project's coding standards and document any changes.

## Acknowledgements

This project was inspired by the need for a simple and effective network diagnostic tool. Special thanks to all contributors.

## Contact

For any questions or concerns, you can reach out at [contact@example.com](mailto\:contact@example.com).

