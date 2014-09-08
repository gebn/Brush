<?php

namespace Crackle\Utilities {

	/**
	 * Extra functionality to aid using the cURL extension.
	 * @author George Brighton
	 */
	class Curl {

		/**
		 * Retrieve the error message corresponding to a CURLE code. These need to be updated every now and again.
		 * Intended to emulate curl_strerror() on PHP <= 5.5.0.
		 * @param int $code The CURLcode whose message to find.
		 * @return string The human-friendly error message corresponding to that code.
		 * @link http://curl.haxx.se/libcurl/c/libcurl-errors.html
		 */
		public static final function getStringError($code) {
			static $definitions = array(
					0 => 'CURLE_OK: All fine. Proceed as usual.',
					1 => 'CURLE_UNSUPPORTED_PROTOCOL: The URL you passed to libcurl used a protocol that this libcurl does not support. The support might be a compile-time option that you didn\'t use, it can be a misspelled protocol string or just a protocol libcurl has no code for.',
					2 => 'CURLE_FAILED_INIT: Very early initialization code failed. This is likely to be an internal error or problem, or a resource problem where something fundamental couldn\'t get done at init time.',
					3 => 'CURLE_URL_MALFORMAT: The URL was not properly formatted.',
					4 => 'CURLE_NOT_BUILT_IN: A requested feature, protocol or option was not found built-in in this libcurl due to a build-time decision. This means that a feature or option was not enabled or explicitly disabled when libcurl was built and in order to get it to function you have to get a rebuilt libcurl.',
					5 => 'CURLE_COULDNT_RESOLVE_PROXY: Couldn\'t resolve proxy. The given proxy host could not be resolved.',
					6 => 'CURLE_COULDNT_RESOLVE_HOST: Couldn\'t resolve host. The given remote host was not resolved.',
					7 => 'CURLE_COULDNT_CONNECT: Failed to connect() to host or proxy.',
					8 => 'CURLE_FTP_WEIRD_SERVER_REPLY: After connecting to a FTP server, libcurl expects to get a certain reply back. This error code implies that it got a strange or bad reply. The given remote server is probably not an OK FTP server.',
					9 => 'CURLE_REMOTE_ACCESS_DENIED: We were denied access to the resource given in the URL. For FTP, this occurs while trying to change to the remote directory.',
					10 => 'CURLE_FTP_ACCEPT_FAILED: While waiting for the server to connect back when an active FTP session is used, an error code was sent over the control connection or similar.',
					11 => 'CURLE_FTP_WEIRD_PASS_REPLY: After having sent the FTP password to the server, libcurl expects a proper reply. This error code indicates that an unexpected code was returned.',
					12 => 'CURLE_FTP_ACCEPT_TIMEOUT: During an active FTP session while waiting for the server to connect, the CURLOPT_ACCEPTTIMOUT_MS (or the internal default) timeout expired.',
					13 => 'CURLE_FTP_WEIRD_PASV_REPLY: libcurl failed to get a sensible result back from the server as a response to either a PASV or a EPSV command. The server is flawed.',
					14 => 'CURLE_FTP_WEIRD_227_FORMAT: FTP servers return a 227-line as a response to a PASV command. If libcurl fails to parse that line, this return code is passed back.',
					15 => 'CURLE_FTP_CANT_GET_HOST: An internal failure to lookup the host used for the new connection.',
					17 => 'CURLE_FTP_COULDNT_SET_TYPE: Received an error when trying to set the transfer mode to binary or ASCII.',
					18 => 'CURLE_PARTIAL_FILE: A file transfer was shorter or larger than expected. This happens when the server first reports an expected transfer size, and then delivers data that doesn\'t match the previously given size.',
					19 => 'CURLE_FTP_COULDNT_RETR_FILE: This was either a weird reply to a \'RETR\' command or a zero byte transfer complete.',
					21 => 'CURLE_QUOTE_ERROR: When sending custom "QUOTE" commands to the remote server, one of the commands returned an error code that was 400 or higher (for FTP) or otherwise indicated unsuccessful completion of the command.',
					22 => 'CURLE_HTTP_RETURNED_ERROR: This is returned if CURLOPT_FAILONERROR is set TRUE and the HTTP server returns an error code that is >= 400.',
					23 => 'CURLE_WRITE_ERROR: An error occurred when writing received data to a local file, or an error was returned to libcurl from a write callback.',
					25 => 'CURLE_UPLOAD_FAILED: Failed starting the upload. For FTP, the server typically denied the STOR command. The error buffer usually contains the server\'s explanation for this.',
					26 => 'CURLE_READ_ERROR: There was a problem reading a local file or an error returned by the read callback.',
					27 => 'CURLE_OUT_OF_MEMORY: A memory allocation request failed. This is serious badness and things are severely screwed up if this ever occurs.',
					28 => 'CURLE_OPERATION_TIMEDOUT: Operation timeout. The specified time-out period was reached according to the conditions.',
					30 => 'CURLE_FTP_PORT_FAILED: The FTP PORT command returned error. This mostly happens when you haven\'t specified a good enough address for libcurl to use. See CURLOPT_FTPPORT.',
					31 => 'CURLE_FTP_COULDNT_USE_REST: The FTP REST command returned error. This should never happen if the server is sane.',
					33 => 'CURLE_RANGE_ERROR: The server does not support or accept range requests.',
					34 => 'CURLE_HTTP_POST_ERROR: This is an odd error that mainly occurs due to internal confusion.',
					35 => 'CURLE_SSL_CONNECT_ERROR: A problem occurred somewhere in the SSL/TLS handshake. You really want the error buffer and read the message there as it pinpoints the problem slightly more. Could be certificates (file formats, paths, permissions), passwords, and others.',
					36 => 'CURLE_BAD_DOWNLOAD_RESUME: The download could not be resumed because the specified offset was out of the file boundary.',
					37 => 'CURLE_FILE_COULDNT_READ_FILE: A file given with FILE:// couldn\'t be opened. Most likely because the file path doesn\'t identify an existing file. Did you check file permissions?',
					38 => 'CURLE_LDAP_CANNOT_BIND: LDAP cannot bind. LDAP bind operation failed.',
					39 => 'CURLE_LDAP_SEARCH_FAILED: LDAP search failed.',
					41 => 'CURLE_FUNCTION_NOT_FOUND: Function not found. A required zlib function was not found.',
					42 => 'CURLE_ABORTED_BY_CALLBACK: Aborted by callback. A callback returned "abort" to libcurl.',
					43 => 'CURLE_BAD_FUNCTION_ARGUMENT: Internal error. A function was called with a bad parameter.',
					45 => 'CURLE_INTERFACE_FAILED: Interface error. A specified outgoing interface could not be used. Set which interface to use for outgoing connections\' source IP address with CURLOPT_INTERFACE.',
					47 => 'CURLE_TOO_MANY_REDIRECTS: Too many redirects. When following redirects, libcurl hit the maximum amount. Set your limit with CURLOPT_MAXREDIRS.',
					48 => 'CURLE_UNKNOWN_OPTION: An option passed to libcurl is not recognized/known. Refer to the appropriate documentation. This is most likely a problem in the program that uses libcurl. The error buffer might contain more specific information about which exact option it concerns.',
					49 => 'CURLE_TELNET_OPTION_SYNTAX: A telnet option string was Illegally formatted.',
					51 => 'CURLE_PEER_FAILED_VERIFICATION: The remote server\'s SSL certificate or SSH md5 fingerprint was deemed not OK.',
					52 => 'CURLE_GOT_NOTHING: Nothing was returned from the server, and under the circumstances, getting nothing is considered an error.',
					53 => 'CURLE_SSL_ENGINE_NOTFOUND: The specified crypto engine wasn\'t found.',
					54 => 'CURLE_SSL_ENGINE_SETFAILED: Failed setting the selected SSL crypto engine as default!',
					55 => 'CURLE_SEND_ERROR: Failed sending network data.',
					56 => 'CURLE_RECV_ERROR: Failure with receiving network data.',
					58 => 'CURLE_SSL_CERTPROBLEM: problem with the local client certificate.',
					59 => 'CURLE_SSL_CIPHER: Couldn\'t use specified cipher.',
					60 => 'CURLE_SSL_CACERT: Peer certificate cannot be authenticated with known CA certificates.',
					61 => 'CURLE_BAD_CONTENT_ENCODING: Unrecognized transfer encoding.',
					62 => 'CURLE_LDAP_INVALID_URL: Invalid LDAP URL.',
					63 => 'CURLE_FILESIZE_EXCEEDED: Maximum file size exceeded.',
					64 => 'CURLE_USE_SSL_FAILED: Requested FTP SSL level failed.',
					65 => 'CURLE_SEND_FAIL_REWIND: When doing a send operation curl had to rewind the data to retransmit, but the rewinding operation failed.',
					66 => 'CURLE_SSL_ENGINE_INITFAILED: Initiating the SSL Engine failed.',
					67 => 'CURLE_LOGIN_DENIED: The remote server denied curl to login (Added in 7.13.1)',
					68 => 'CURLE_TFTP_NOTFOUND: File not found on TFTP server.',
					69 => 'CURLE_TFTP_PERM: Permission problem on TFTP server.',
					70 => 'CURLE_REMOTE_DISK_FULL: Out of disk space on the server.',
					71 => 'CURLE_TFTP_ILLEGAL: Illegal TFTP operation.',
					72 => 'CURLE_TFTP_UNKNOWNID: Unknown TFTP transfer ID.',
					73 => 'CURLE_REMOTE_FILE_EXISTS: File already exists and will not be overwritten.',
					74 => 'CURLE_TFTP_NOSUCHUSER: This error should never be returned by a properly functioning TFTP server.',
					75 => 'CURLE_CONV_FAILED: Character conversion failed.',
					76 => 'CURLE_CONV_REQD: Caller must register conversion callbacks.',
					77 => 'CURLE_SSL_CACERT_BADFILE: Problem with reading the SSL CA cert (path? access rights?)',
					78 => 'CURLE_REMOTE_FILE_NOT_FOUND: The resource referenced in the URL does not exist.',
					79 => 'CURLE_SSH: An unspecified error occurred during the SSH session.',
					80 => 'CURLE_SSL_SHUTDOWN_FAILED: Failed to shut down the SSL connection.',
					81 => 'CURLE_AGAIN: Socket is not ready for send/recv wait till it\'s ready and try again. This return code is only returned from curl_easy_recv(3) and curl_easy_send(3) (Added in 7.18.2)',
					82 => 'CURLE_SSL_CRL_BADFILE: Failed to load CRL file (Added in 7.19.0)',
					83 => 'CURLE_SSL_ISSUER_ERROR: Issuer check failed (Added in 7.19.0)',
					84 => 'CURLE_FTP_PRET_FAILED: The FTP server does not understand the PRET command at all or does not support the given argument. Be careful when using CURLOPT_CUSTOMREQUEST, a custom LIST command will be sent with PRET CMD before PASV as well. (Added in 7.20.0)',
					85 => 'CURLE_RTSP_CSEQ_ERROR: Mismatch of RTSP CSeq numbers.',
					86 => 'CURLE_RTSP_SESSION_ERROR: Mismatch of RTSP Session Identifiers.',
					87 => 'CURLE_FTP_BAD_FILE_LIST: Unable to parse FTP file list (during FTP wildcard downloading).',
					88 => 'CURLE_CHUNK_FAILED: Chunk callback reported error.');

			if(isset($definitions[$code])) {
				return $definitions[$code];
			}

			return 'Unknown error.';
		}
	}
}
