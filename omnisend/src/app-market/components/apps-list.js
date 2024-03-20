import {
	Button,
	Card,
	CardHeader,
	CardBody,
	CardFooter,
	Flex,
	__experimentalSpacer as Spacer,
	__experimentalText as Text,
	__experimentalHeading as Heading,
} from "@wordpress/components";

const AppsList = ({ apps, categoryName, categoryDescription }) => {
	const navigateToPluginPage = (url) => {
		window.open(url, "_blank").focus();
	};

	return (
		<>
			<Spacer marginBottom={6}>
				{categoryName && <Heading>{categoryName}</Heading>}
				{categoryDescription && <Text>{categoryDescription}</Text>}
			</Spacer>
			<Flex
				gap={6}
				wrap={true}
				justify="start"
				className="omnisend-apps-list-container"
			>
				{apps &&
					apps.map((app) => (
						<Card
							key={app.slug}
							size={"medium"}
							isBorderless={true}
							backgroundSize={50}
							className="omnisend-apps-list-card"
						>
							<Flex direction="column">
								<CardHeader isBorderless="true">
									<Flex direction="column">
										<img
											className="omnisend-apps-list-card-logo"
											src={app.logo}
										/>
										<Heading level={4}>{app.name}</Heading>
										<Text size={12}>by {app.created_by}</Text>
									</Flex>
								</CardHeader>
								<CardBody className="omnisend-apps-list-card-description-container">
									<Text size={14}>{app.description}</Text>
								</CardBody>
								<CardFooter isBorderless={true}>
									<Button
										variant="primary"
										onClick={() => navigateToPluginPage(app.url)}
									>
										Add this add-on
									</Button>
								</CardFooter>
							</Flex>
						</Card>
					))}
			</Flex>
		</>
	);
};

export default AppsList;
