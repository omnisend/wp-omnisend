import { useState } from "@wordpress/element";
import
{
	Button,
	Flex,
	FlexItem,
	TextControl,
	__experimentalText as Text,
	__experimentalSpacer as Spacer,
} from "@wordpress/components";

const ConnectionSteps = ( { onSubmit } ) =>
{
	const [ apiKey, setApiKey ] = useState( null );

	const navigateToExternalUrl = ( url ) =>
	{
		window.open( url, "_blank" ).focus();
	};

	return (
		<>
			<Spacer marginTop={8} marginBottom={8}>
				<Spacer marginBottom={5}>
					<Text size={16}>1. Create Omnisend account</Text>
				</Spacer>
				<Button
					variant="secondary"
					onClick={() =>
						navigateToExternalUrl(
							"https://app.omnisend.com/registrationv2?utm_source=wordpress_plugin",
						)
					}
				>
					Go to Omnisend
				</Button>
			</Spacer>
			<hr />
			<Spacer marginTop={8} marginBottom={8}>
				<Spacer marginBottom={5}>
					<Text size={16}>2. Go to API keys section and create API key</Text>
				</Spacer>
				<Button
					variant="secondary"
					onClick={() =>
						navigateToExternalUrl(
							"https://app.omnisend.com/account/api-keys?utm_source=wordpress_plugin",
						)
					}
				>
					Go to API keys
				</Button>
			</Spacer>
			<hr />
			<Spacer marginTop={8} marginBottom={8}>
				<Spacer marginBottom={5}>
					<Text size={16}>3. Paste created API key here:</Text>
				</Spacer>
				<Flex align={"'start'"} gap={4} wrap="true">
					<FlexItem display="flex" className="omnisend-connection-input-wrap">
						<TextControl
							value={apiKey}
							className="omnisend-connection-input"
							onChange={( nextValue ) => setApiKey( nextValue ?? "" )}
						/>
					</FlexItem>
					<FlexItem>
						<Button
							disabled={!apiKey}
							variant="primary"
							size="compact"
							type="submit"
							onClick={() => onSubmit( apiKey )}
						>
							Connect Omnisend
						</Button>
					</FlexItem>
				</Flex>
			</Spacer>
		</>
	);
};

export default ConnectionSteps;
