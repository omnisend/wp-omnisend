import {
	Card,
	CardBody,
	Flex,
	__experimentalText as Text,
	__experimentalHeading as Heading,
} from "@wordpress/components";

const AppsListNotice = () => {
	return (
		<Card isBorderless={true} size="large">
			<CardBody isBorderless={true}>
				<Flex direction="column">
					<Heading level={1}>Omnisend Add-Ons</Heading>
					<Text className="omnisend-apps-list-notice-text" size={14}>
						You can expand the possibilities of Omnisend by integrating it with
						additional add-ons.
					</Text>
				</Flex>
			</CardBody>
		</Card>
	);
};

export default AppsListNotice;
