<?php
// by Edd Dumbill (C) 1999-2002
// <edd@usefulinc.com>
// $Id: xmlrpcs.inc,v 1.71 2008/10/29 23:41:28 ggiunta Exp $

// Copyright (c) 1999,2000,2002 Edd Dumbill.
// All rights reserved.
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions
// are met:
//
//    * Redistributions of source code must retain the above copyright
//      notice, this list of conditions and the following disclaimer.
//
//    * Redistributions in binary form must reproduce the above
//      copyright notice, this list of conditions and the following
//      disclaimer in the documentation and/or other materials provided
//      with the distribution.
//
//    * Neither the name of the "XML-RPC for PHP" nor the names of its
//      contributors may be used to endorse or promote products derived
//      from this software without specific prior written permission.
//
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
// "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
// LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
// FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
// REGENTS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
// INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
// (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
// SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
// HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
// STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
// ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
// OF THE POSSIBILITY OF SUCH DAMAGE.

	// XML RPC Server class
	// requires: xmlrpc.inc

	$GLOBALS['xmlrpcs_capabilities'] = array(
		// xmlrpc spec: always supported
		'xmlrpc' => new xmlrpcval(array(
			'specUrl' => new xmlrpcval('http://www.xmlrpc.com/spec', 'string'),
			'specVersion' => new xmlrpcval(1, 'int')
		), 'struct'),
		// if we support system.xxx functions, we always support multicall, too...
		// Note that, as of 2006/09/17, the following URL does not respond anymore
		'system.multicall' => new xmlrpcval(array(
			'specUrl' => new xmlrpcval('http://www.xmlrpc.com/discuss/msgReader$1208', 'string'),
			'specVersion' => new xmlrpcval(1, 'int')
		), 'struct'),
		// introspection: version 2! we support 'mixed', too
		'introspection' => new xmlrpcval(array(
			'specUrl' => new xmlrpcval('http://phpxmlrpc.sourceforge.net/doc-2/ch10.html', 'string'),
			'specVersion' => new xmlrpcval(2, 'int')
		), 'struct')
	);

	/* Functions that implement system.XXX methods of xmlrpc servers */
	$_xmlrpcs_getCapabilities_sig=array(array($GLOBALS['xmlrpcStruct']));
	$_xmlrpcs_getCapabilities_doc='This method lists all the capabilites that the XML-RPC server has: the (more or less standard) extensions to the xmlrpc spec that it adheres to';
	$_xmlrpcs_getCapabilities_sdoc=array(array('list of capabilities, described as structs with a version number and url for the spec'));
	function _xmlrpcs_getCapabilities($server, $m=null)
	{
		$outAr = $GLOBALS['xmlrpcs_capabilities'];
		// NIL extension
		if ($GLOBALS['xmlrpc_null_extension']) {
			$outAr['nil'] = new xmlrpcval(array(
				'specUrl' => new xmlrpcval('http://www.ontosys.com/xml-rpc/extensions.php', 'string'),
				'specVersion' => new xmlrpcval(1, 'int')
			), 'struct');
		}
		return new xmlrpcresp(new xmlrpcval($outAr, 'struct'));
	}

	// listMethods: signature was either a string, or nothing.
	// The useless string variant has been removed
	$_xmlrpcs_listMethods_sig=array(array($GLOBALS['xmlrpcArray']));
	$_xmlrpcs_listMethods_doc='This method lists all the methods that the XML-RPC server knows how to dispatch';
	$_xmlrpcs_listMethods_sdoc=array(array('list of method names'));
	function _xmlrpcs_listMethods($server, $m=null) // if called in plain php values mode, second param is missing
	{

		$outAr=array();
		foreach($server->dmap as $key => $val)
		{
			$outAr[]=new xmlrpcval($key, 'string');
		}
		if($server->allow_system_funcs)
		{
			foreach($GLOBALS['_xmlrpcs_dmap'] as $key => $val)
			{
				$outAr[]=new xmlrpcval($key, 'string');
			}
		}
		return new xmlrpcresp(new xmlrpcval($outAr, 'array'));
	}

	$_xmlrpcs_methodSignature_sig=array(array($GLOBALS['xmlrpcArray'], $GLOBALS['xmlrpcString']));
	$_xmlrpcs_methodSignature_doc='Returns an array of known signatures (an array of arrays) for the method name passed. If no signatures are known, returns a none-array (test for type != array to detect missing signature)';
	$_xmlrpcs_methodSignature_sdoc=array(array('list of known signatures, each sig being an array of xmlrpc type names', 'name of method to be described'));
	function _xmlrpcs_methodSignature($server, $m)
	{
		// let accept as parameter both an xmlrpcval or string
		if (is_object($m))
		{
			$methName=$m->getParam(0);
			$methName=$methName->scalarval();
		}
		else
		{
			$methName=$m;
		}
		if(strpos($methName, "system.") === 0)
		{
			$dmap=$GLOBALS['_xmlrpcs_dmap']; $sysCall=1;
		}
		else
		{
			$dmap=$server->dmap; $sysCall=0;
		}
		if(isset($dmap[$methName]))
		{
			if(isset($dmap[$methName]['signature']))
			{
				$sigs=array();
				foreach($dmap[$methName]['signature'] as $inSig)
				{
					$cursig=array();
					foreach($inSig as $sig)
					{
						$cursig[]=new xmlrpcval($sig, 'string');
					}
					$sigs[]=new xmlrpcval($cursig, 'array');
				}
				$r=new xmlrpcresp(new xmlrpcval($sigs, 'array'));
			}
			else
			{
				// NB: according to the official docs, we should be returning a
				// "none-array" here, which means not-an-array
				$r=new xmlrpcresp(new xmlrpcval('undef', 'string'));
			}
		}
		else
		{
			$r=new xmlrpcresp(0,$GLOBALS['xmlrpcerr']['introspect_unknown'], $GLOBALS['xmlrpcstr']['introspect_unknown']);
		}
		return $r;
	}

	$_xmlrpcs_methodHelp_sig=array(array($GLOBALS['xmlrpcString'], $GLOBALS['xmlrpcString']));
	$_xmlrpcs_methodHelp_doc='Returns help text if defined for the method passed, otherwise returns an empty string';
	$_xmlrpcs_methodHelp_sdoc=array(array('method description', 'name of the method to be described'));
	function _xmlrpcs_methodHelp($server, $m)
	{
		// let accept as parameter both an xmlrpcval or string
		if (is_object($m))
		{
			$methName=$m->getParam(0);
			$methName=$methName->scalarval();
		}
		else
		{
			$methName=$m;
		}
		if(strpos($methName, "system.") === 0)
		{
			$dmap=$GLOBALS['_xmlrpcs_dmap']; $sysCall=1;
		}
		else
		{
			$dmap=$server->dmap; $sysCall=0;
		}
		if(isset($dmap[$methName]))
		{
			if(isset($dmap[$methName]['docstring']))
			{
				$r=new xmlrpcresp(new xmlrpcval($dmap[$methName]['docstring']), 'string');
			}
			else
			{
				$r=new xmlrpcresp(new xmlrpcval('', 'string'));
			}
		}
		else
		{
			$r=new xmlrpcresp(0, $GLOBALS['xmlrpcerr']['introspect_unknown'], $GLOBALS['xmlrpcstr']['introspect_unknown']);
		}
		return $r;
	}

	$_xmlrpcs_multicall_sig = array(array($GLOBALS['xmlrpcArray'], $GLOBALS['xmlrpcArray']));
	$_xmlrpcs_multicall_doc = 'Boxcar multiple RPC calls in one request. See http://www.xmlrpc.com/discuss/msgReader$1208 for details';
	$_xmlrpcs_multicall_sdoc = array(array('list of response structs, where each struct has the usual members', 'list of calls, with each call being represented as a struct, with members "methodname" and "params"'));
	function _xmlrpcs_multicall_error($err)
	{
		if(is_string($err))
		{
			$str = $GLOBALS['xmlrpcstr']["multicall_${err}"];
			$code = $GLOBALS['xmlrpcerr']["multicall_${err}"];
		}
		else
		{
			$code = $err->faultCode();
			$str = $err->faultString();
		}
		$struct = array();
		$struct['faultCode'] = new xmlrpcval($code, 'int');
		$struct['faultString'] = new xmlrpcval($str, 'string');
		return new xmlrpcval($struct, 'struct');
	}

	function _xmlrpcs_multicall_do_call($server, $call)
	{
		if($call->kindOf() != 'struct')
		{
			return _xmlrpcs_multicall_error('notstruct');
		}
		$methName = @$call->structmem('methodName');
		if(!$methName)
		{
			return _xmlrpcs_multicall_error('nomethod');
		}
		if($methName->kindOf() != 'scalar' || $methName->scalartyp() != 'string')
		{
			return _xmlrpcs_multicall_error('notstring');
		}
		if($methName->scalarval() == 'system.multicall')
		{
			return _xmlrpcs_multicall_error('recursion');
		}

		$params = @$call->structmem('params');
		if(!$params)
		{
			return _xmlrpcs_multicall_error('noparams');
		}
		if($params->kindOf() != 'array')
		{
			return _xmlrpcs_multicall_error('notarray');
		}
		$numParams = $params->arraysize();

		$msg = new xmlrpcmsg($methName->scalarval());
		for($i = 0; $i < $numParams; $i++)
		{
			if(!$msg->addParam($params->arraymem($i)))
			{
				$i++;
				return _xmlrpcs_multicall_error(new xmlrpcresp(0,
					$GLOBALS['xmlrpcerr']['incorrect_params'],
					$GLOBALS['xmlrpcstr']['incorrect_params'] . ": probable xml error in param " . $i));
			}
		}

		$result = $server->execute($msg);

		if($result->faultCode() != 0)
		{
			return _xmlrpcs_multicall_error($result);		// Method returned fault.
		}

		return new xmlrpcval(array($result->value()), 'array');
	}

	function _xmlrpcs_multicall_do_call_phpvals($server, $call)
	{
		if(!is_array($call))
		{
			return _xmlrpcs_multicall_error('notstruct');
		}
		if(!array_key_exists('methodName', $call))
		{
			return _xmlrpcs_multicall_error('nomethod');
		}
		if (!is_string($call['methodName']))
		{
			return _xmlrpcs_multicall_error('notstring');
		}
		if($call['methodName'] == 'system.multicall')
		{
			return _xmlrpcs_multicall_error('recursion');
		}
		if(!array_key_exists('params', $call))
		{
			return _xmlrpcs_multicall_error('noparams');
		}
		if(!is_array($call['params']))
		{
			return _xmlrpcs_multicall_error('notarray');
		}

		// this is a real dirty and simplistic hack, since we might have received a
		// base64 or datetime values, but they will be listed as strings here...
		$numParams = count($call['params']);
		$pt = array();
		foreach($call['params'] as $val)
			$pt[] = php_2_xmlrpc_type(gettype($val));

		$result = $server->execute($call['methodName'], $call['params'], $pt);

		if($result->faultCode() != 0)
		{
			return _xmlrpcs_multicall_error($result);		// Method returned fault.
		}

		return new xmlrpcval(array($result->value()), 'array');
	}

	function _xmlrpcs_multicall($server, $m)
	{
		$result = array();
		// let accept a plain list of php parameters, beside a single xmlrpc msg object
		if (is_object($m))
		{
			$calls = $m->getParam(0);
			$numCalls = $calls->arraysize();
			for($i = 0; $i < $numCalls; $i++)
			{
				$call = $calls->arraymem($i);
				$result[$i] = _xmlrpcs_multicall_do_call($server, $call);
			}
		}
		else
		{
			$numCalls=count($m);
			for($i = 0; $i < $numCalls; $i++)
			{
				$result[$i] = _xmlrpcs_multicall_do_call_phpvals($server, $m[$i]);
			}
		}

		return new xmlrpcresp(new xmlrpcval($result, 'array'));
	}

	$GLOBALS['_xmlrpcs_dmap']=array(
		'system.listMethods' => array(
			'function' => '_xmlrpcs_listMethods',
			'signature' => $_xmlrpcs_listMethods_sig,
			'docstring' => $_xmlrpcs_listMethods_doc,
			'signature_docs' => $_xmlrpcs_listMethods_sdoc),
		'system.methodHelp' => array(
			'function' => '_xmlrpcs_methodHelp',
			'signature' => $_xmlrpcs_methodHelp_sig,
			'docstring' => $_xmlrpcs_methodHelp_doc,
			'signature_docs' => $_xmlrpcs_methodHelp_sdoc),
		'system.methodSignature' => array(
			'function' => '_xmlrpcs_methodSignature',
			'signature' => $_xmlrpcs_methodSignature_sig,
			'docstring' => $_xmlrpcs_methodSignature_doc,
			'signature_docs' => $_xmlrpcs_methodSignature_sdoc),
		'system.multicall' => array(
			'function' => '_xmlrpcs_multicall',
			'signature' => $_xmlrpcs_multicall_sig,
			'docstring' => $_xmlrpcs_multicall_doc,
			'signature_docs' => $_xmlrpcs_multicall_sdoc),
		'system.getCapabilities' => array(
			'function' => '_xmlrpcs_getCapabilities',
			'signature' => $_xmlrpcs_getCapabilities_sig,
			'docstring' => $_xmlrpcs_getCapabilities_doc,
			'signature_docs' => $_xmlrpcs_getCapabilities_sdoc)
	);

	$GLOBALS['_xmlrpcs_occurred_errors'] = '';
	$GLOBALS['_xmlrpcs_prev_ehandler'] = '';

	/**
	* Error handler used to track errors that occur during server-side execution of PHP code.
	* This allows to report back to the client whether an internal error has occurred or not
	* using an xmlrpc response object, instead of letting the client deal with the html junk
	* that a PHP execution error on the server generally entails.
	*
	* NB: in fact a user defined error handler can only handle WARNING, NOTICE and USER_* errors.
	*
	*/
	function _xmlrpcs_errorHandler($errcode, $errstring, $filename=null, $lineno=null, $context=null)
	{
		// obey the @ protocol
		if (error_reporting() == 0)
			return;

		//if($errcode != E_NOTICE && $errcode != E_WARNING && $errcode != E_USER_NOTICE && $errcode != E_USER_WARNING)
		if($errcode != E_STRICT)
		{
			$GLOBALS['_xmlrpcs_occurred_errors'] = $GLOBALS['_xmlrpcs_occurred_errors'] . $errstring . "\n";
		}
		// Try to avoid as much as possible disruption to the previous error handling
		// mechanism in place
		if($GLOBALS['_xmlrpcs_prev_ehandler'] == '')
		{
			// The previous error handler was the default: all we should do is log error
			// to the default error log (if level high enough)
			if(ini_get('log_errors') && (intval(ini_get('error_reporting')) & $errcode))
			{
				error_log($errstring);
			}
		}
		else
		{
			// Pass control on to previous error handler, trying to avoid loops...
			if($GLOBALS['_xmlrpcs_prev_ehandler'] != '_xmlrpcs_errorHandler')
			{
				// NB: this code will NOT work on php < 4.0.2: only 2 params were used for error handlers
				if(is_array($GLOBALS['_xmlrpcs_prev_ehandler']))
				{
					// the following works both with static class methods and plain object methods as error handler
					call_user_func_array($GLOBALS['_xmlrpcs_prev_ehandler'], array($errcode, $errstring, $filename, $lineno, $context));
				}
				else
				{
					$GLOBALS['_xmlrpcs_prev_ehandler']($errcode, $errstring, $filename, $lineno, $context);
				}
			}
		}
	}

	$GLOBALS['_xmlrpc_debuginfo']='';

	/**
	* Add a string to the debug info that can be later seralized by the server
	* as part of the response message.
	* Note that for best compatbility, the debug string should be encoded using
	* the $GLOBALS['xmlrpc_internalencoding'] character set.
	* @param string $m
	* @access public
	*/
	function xmlrpc_debugmsg($m)
	{
		$GLOBALS['_xmlrpc_debuginfo'] .= $m . "\n";
	}

	class xmlrpc_server
	{
		/**
		* Array defining php functions exposed as xmlrpc methods by this server
		* @access private
		*/
		var $dmap=array();
		/**
		* Defines how functions in dmap will be invoked: either using an xmlrpc msg object
		* or plain php values.
		* valid strings are 'xmlrpcvals', 'phpvals' or 'epivals'
		*/
		var $functions_parameters_type='xmlrpcvals';
		/**
		* Option used for fine-tuning the encoding the php values returned from
		* functions registered in the dispatch map when the functions_parameters_types
		* member is set to 'phpvals'
		* @see php_xmlrpc_encode for a list of values
		*/
		var $phpvals_encoding_options = array( 'auto_dates' );
		/// controls wether the server is going to echo debugging messages back to the client as comments in response body. valid values: 0,1,2,3
		var $debug = 1;
		/**
		* Controls behaviour of server when invoked user function throws an exception:
		* 0 = catch it and return an 'internal error' xmlrpc response (default)
		* 1 = catch it and return an xmlrpc response with the error corresponding to the exception
		* 2 = allo
                 * 
                 */
        }