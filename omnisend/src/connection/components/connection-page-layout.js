import ConnectionLogos from "./connection-logos";
import ConnectionFeatures from "./connection-features";
import ConnectionSteps from "./connection-steps";
import { useState } from "@wordpress/element";
import
{
	Notice,
	Flex,
	Spinner,
	__experimentalSpacer as Spacer,
	__experimentalHeading as Heading,
} from "@wordpress/components";

const ConnectionPageLayout = () =>
{
	const [ error, setError ] = useState( null );
	const [ loading, setLoading ] = useState( null );

	const connect = ( apiKey ) =>
	{
		const fd = new FormData();
		fd.append( "api_key", apiKey );
		setLoading( true );
		fetch( "/wp-json/omnisend/v1/connect", {
			method: "POST",
			body: fd,
		} )
			.then( ( response ) => response.json() )
			.then( ( data ) =>
			{
				if ( data.success )
				{
					location.reload();
				}
				if ( data.error )
				{
					setError( data.error );
					setLoading( false );
				}
			} )
			.catch( ( e ) =>
			{
				setError( e.message || e );
				setLoading( false );
			} );
	};

	if ( loading )
	{
		return (
			<Flex justify="center">
				<Spacer marginTop={8}>
					<Spinner />
				</Spacer>
			</Flex>
		);
	}

	return (
		<>
			<div className="omnisend-page-layout">
				{error && (
					<Spacer marginBottom={8}>
						<Notice status="error">{error}</Notice>
					</Spacer>
				)}
				<ConnectionLogos />
				<Spacer marginTop={8} marginBottom={6}>
					<Heading level={1}>Connect Omnisend plugin</Heading>
				</Spacer>
				<ConnectionFeatures />
				<Spacer marginTop={16} marginBottom={16}>
					<hr />
				</Spacer>
				<Heading level={2}>Steps to connect to Omnisend:</Heading>
				<ConnectionSteps onSubmit={connect} />
			</div>
		</>
	);
};

export default ConnectionPageLayout;
