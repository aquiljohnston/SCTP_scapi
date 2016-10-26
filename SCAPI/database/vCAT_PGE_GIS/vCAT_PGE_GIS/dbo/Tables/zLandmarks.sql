CREATE TABLE [dbo].[zLandmarks] (
    [ID]           INT           IDENTITY (1, 1) NOT NULL,
    [LandmarkName] VARCHAR (100) NULL,
    [Location]     VARCHAR (50)  NULL,
    [Latitude]     FLOAT (53)    NULL,
    [Longitude]    FLOAT (53)    NULL
);

